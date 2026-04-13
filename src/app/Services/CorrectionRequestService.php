<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Presenters\BasePresenter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CorrectionRequestService
{
    /**
     * 【理由】複数スタッフの未承認申請をまとめて取得し、管理者が最新の申請状況を確認しやすくするため。
     * 【制約】$userIds が有効なユーザーIDの配列であり、pending ステータスが正しく定義されている必要がある。
     * 【注意】attendance を eager load するため、関連データの不整合があると例外が発生する可能性がある。
     */
    public function getPendingRequestsByUser($userIds)
    {
        return CorrectionRequest::pending()
            ->where('user_id', (array)$userIds)
            ->with('attendance')
            ->latest('created_at')
            ->get();
    }

    /**
     * 【理由】承認済み申請のみを抽出し、履歴確認用の一覧として利用できるようにするため。
     * 【制約】$userIds が有効なユーザーIDの配列であり、approved ステータスが正しく定義されている必要がある。
     * 【注意】大量データを取得する場合、created_at 降順での並び替えがクエリ負荷に影響する可能性がある。
     */
    public function getApprovedRequestsByUser($userIds)
    {
        return CorrectionRequest::approved()
            ->where('user_id', (array)$userIds)
            ->with('attendance')
            ->latest('created_at')
            ->get();
    }

    /**
     * 【理由】修正前後の値を比較し、実質的な変更がある場合のみ申請を作成するため。
     * 【制約】勤怠データと休憩ログの構造が揃っていることを前提に比較用の before 値を構築する。
     * 【注意】承認済み申請が存在する場合は比較基準が変わるため、差分判定の結果が状況に依存する。
     */
    public function createCorrectionRequest(Attendance $attendance, array $data)
    {
        $format = function($value) {
            if (is_array($value)) {

                $normalized = array_map(function ($breaks) {
                    return [
                        'end'   => isset($breaks['end'])   ? date('H:i', strtotime($breaks['end']))   : '',
                        'start' => isset($breaks['start']) ? date('H:i', strtotime($breaks['start'])) : '',
                    ];
                }, $value);
                return json_encode($normalized);
            }
            return ($value && preg_match('/^\d{2}:\d{2}/', $value)) ? date('H:i', strtotime($value)) : (string)$value;
        };

        $before = [
            'clock_in'  => BasePresenter::formatTime($attendance->clock_in),
            'clock_out' => BasePresenter::formatTime($attendance->clock_out),
            'breaks'    => $attendance->breakLogs->map(function ($break) {
                return [
                    'start' => BasePresenter::formatTime($break->break_start),
                    'end'   => BasePresenter::formatTime($break->break_end),
                ];
            })->toArray(),
            'remarks'   => $attendance->remarks,
        ];

        $after = [
            'clock_in'  => $data['clock_in']  ?: $before['clock_in'],
            'clock_out' => $data['clock_out'] ?: $before['clock_out'],
            'breaks'    => $data['breaks']    ?? $before['breaks'],
            'remarks'   => $data['remarks']   ?? $before['remarks'],
        ];

        $latestApproved = CorrectionRequest::where('attendance_id', $attendance->id)
            ->approved()->latest()->first();

        $baseValues = $latestApproved ? $latestApproved->after_value : $before;

        $baseComparison = array_map($format, $baseValues);
        $requestComparison = array_map($format, $after);

        $diffKeys = [];
        foreach ($requestComparison as $key => $value) {
            if ($value !== ($baseComparison[$key] ?? null)) {
                $diffKeys[] = $key;
            }
        }

        if (empty($diffKeys)) {
            $errorMessage = $latestApproved
            ? '承認済みの内容と同じため再申請できません'
            : '変更がありません 修正申請を送信できません';

            throw new \Exception($errorMessage);
        }

        return CorrectionRequest::create([
            'user_id'       => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'request_type'  => count($diffKeys) === 1 ? $diffKeys[0] : 'multiple',
            'before_value'  => $before,
            'after_value'   => $after,
            'status'        => CorrectionRequest::STATUS_PENDING,
            'remarks'       => $data['remarks'] ?? null,
        ]);
    }

    /**
     * 【理由】ID から修正申請を取得し、承認ロジックを共通メソッドへ委譲することで処理の一貫性を保つため。
     * 【制約】$id は存在する修正申請の ID であり、$approver は承認権限を持つ User インスタンスである必要がある。
     * 【注意】findOrFail により不正 ID は例外となるため、呼び出し側での例外ハンドリング方針に依存する。
     */
    public function approveById($id, User $approver)
    {
        $correction = CorrectionRequest::findOrFail($id);
        return $this->approve($correction, $approver);
    }


    /**
     * 【理由】修正申請の after_value を元に勤怠データと休憩ログを一括更新し、承認者情報と承認日時を記録するため。
     * 【制約】$request は attendance リレーションと after_value を持つ CorrectionRequest インスタンスであり、
     *        $approver は承認権限を持つ User インスタンスである必要がある。
     * 【注意】休憩ログは最大2件を前提としており、件数が異なる場合は更新されないため、
     *        データ構造の変更時はこの処理も合わせて修正が必要。
     */
    public function approve(CorrectionRequest $request, User $approver):void
    {
        $after = $request->after_value;

        $attendance = $request->attendance()->with('breakLogs')->first();

        DB::transaction(function () use ($attendance, $after, $request, $approver) {

            $attendance->update([
                'clock_in'  => Carbon::parse($after['clock_in'])->format('H:i'),
                'clock_out' => Carbon::parse($after['clock_out'])->format('H:i'),
                'remarks'   => $after['remarks'],
            ]);

            $attendance->breakLogs()->delete();

            foreach ($after['breaks'] as $break) {
                $attendance->breakLogs()->create([
                    'break_start' => $break['start'],
                    'break_end'   => $break['end'],
                ]);
            }

            $attendance->refresh();

            $request->update([
                'status'      => CorrectionRequest::STATUS_APPROVED,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
        });
    }
}
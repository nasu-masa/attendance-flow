<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Presenters\BasePresenter;
use Illuminate\Support\Facades\DB;

class CorrectionRequestService
{
    /* ================================
        未承認の申請
    ================================= */

    public function getPendingRequestsByUser($userIds)
    {
        return CorrectionRequest::pending()
            ->where('user_id', (array)$userIds)
            ->with('attendance')
            ->latest('created_at')
            ->get();
    }

    /* ================================
        承認済みの申請
    ================================= */

    public function getApprovedRequestsByUser($userIds)
    {
        return CorrectionRequest::approved()
            ->where('user_id', (array)$userIds)
            ->with('attendance')
            ->latest('created_at')
            ->get();
    }

    /* ================================
        修正申請の作成
    ================================= */

    public function createCorrectionRequest(Attendance $attendance, array $data)
    {
        $break1 = $attendance->breakLogs->get(0);
        $break2 = $attendance->breakLogs->get(1);

        $before = [
            'clock_in'      => BasePresenter::formatTime($attendance->clock_in),
            'clock_out'     => BasePresenter::formatTime($attendance->clock_out),
            'break_start_1' => BasePresenter::formatTime($break1?->break1?->break_start),
            'break_end_1'   => BasePresenter::formatTime($break1?->break1?->break_end),
            'break_start_2' => BasePresenter::formatTime($break2?->break2?->break_start),
            'break_end_2'   => BasePresenter::formatTime($break2?->break2?->break_end),
            'remarks'       => $attendance->remarks,
        ];

        $after = array_intersect_key($data, $before);

        $toString = fn($value) => is_null($value)
            ? ''
            : (string)$value;

        $requestValueString = array_map($toString, $after);

        $latestApproved = CorrectionRequest::where('attendance_id', $attendance->id)
            ->approved()->latest()->first();

        if ($latestApproved) {
            // 前回の承認があるなら、誤申請を防ぐためその「承認された後の値」を基準にする
            $baseValue = array_map($toString, $latestApproved->after_value);
            $errorMessage = '承認済みの内容と同じため再申請できません';
        } else {
            $baseValue = array_map($toString, $before);
            $errorMessage = '変更がありません 修正申請を送信できません';
        }

        $diffKeys = array_keys((new CorrectionRequest)->diff($baseValue, $requestValueString));

        // 変更がない場合は差分が空になるため、エラーを投げる
        if (empty($diffKeys)) {
            throw new \Exception($errorMessage);
        }

        $requestType = count($diffKeys) === 1 ? $diffKeys[0] : 'multiple';

        return CorrectionRequest::create([
            'user_id'       => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'request_type'  => $requestType,
            'before_value'  => $before,
            'after_value'   => $after,
            'status'        => CorrectionRequest::STATUS_PENDING,
            'remarks'       => $data['remarks'] ?? null,
        ]);
    }


    /* ================================
        修正申請の承認（ID 指定）
    ================================= */

    public function approveById($id, User $approver)
    {
        $correction = CorrectionRequest::findOrFail($id);
        return $this->approve($correction, $approver);
    }

    /* ================================
        修正申請の承認
    ================================= */

    public function approve(CorrectionRequest $request, User $approver)
    {
        $after = $request->after_value;

        $attendance = $request->attendance()->with('breakLogs')->first();

        DB::transaction(function () use ($attendance, $after, $request, $approver) {

            $attendance->update([
                'clock_in'  => $after['clock_in'],
                'clock_out' => $after['clock_out'],
                'remarks'   => $after['remarks'],
            ]);

            $break1 = $attendance->breakLogs->get(0);
            if ($break1) {
                $break1->update([
                    'break_start' => $after['break_start_1'],
                    'break_end'   => $after['break_end_1'],
                ]);
            }

            $break2 = $attendance->breakLogs->get(1);
            if ($break2) {
                $break2->update([
                    'break_start' => $after['break_start_2'],
                    'break_end'   => $after['break_end_2'],
                ]);
            }

            $request->update([
                'status'      => CorrectionRequest::STATUS_APPROVED,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
        });
    }
}
<?php

namespace App\Presenters;

use App\Models\CorrectionRequest;

class CorrectionRequestListPresenter extends BasePresenter
{
    /**
     * 【理由】タブ状態と整形済み申請データをまとめ、ビュー側が条件分岐を持たずに一覧を描画できるようにするため。
     * 【制約】$tab が定義済みステータスであり、$requests が整形可能なコレクションである必要がある。
     * 【注意】ステータスが追加された場合はタブ判定の更新が必要となり、表示ロジックに影響が出る。
     */

    public static function make($requests, $tab)
    {
        return [
            'tab'       => $tab,
            'isPending' => ($tab === CorrectionRequest::STATUS_PENDING),
            'isApproved' => ($tab === CorrectionRequest::STATUS_APPROVED),
            'requests'  => self::formatRequests($requests),
        ];
    }

    /**
     * 【理由】申請データを一覧表示に適した形式へ変換し、ビューが生データ構造に依存しないようにするため。
     * 【制約】関連モデル（user・attendance）が取得済みであることを前提に、必要項目を安全に参照する。
     * 【注意】ラベルや日付フォーマットは設定値に依存するため、設定変更時は表示内容が変わる点に注意。
     */
    protected static function formatRequests($requests)
    {
        return collect($requests)->map(function ($req) {

            $labels = config('attendance.request_status_labels');
            $statusLabel = $labels[$req->status] ?? $req->status;

            return [
                'request_id'     => $req->id,
                'attendance_id'  => $req->attendance?->id,

                'status_label'   => $statusLabel,

                'user_name'      => self::limit($req->user->name, 9),
                'user_name_full' => $req->user->name,

                'target_date'    => $req->attendance?->date?->locale('ja')->isoFormat('YYYY/MM/DD')  ?? '',

                'remarks'        => self::limit($req->remarks, 10),
                'remarks_full'   => $req->remarks,

                'request_date' => $req->created_at?->locale('ja')->isoFormat('YYYY/MM/DD') ?? '',
            ];
        });
    }
}

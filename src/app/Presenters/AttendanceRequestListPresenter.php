<?php

namespace App\Presenters;

use App\Models\CorrectionRequest;

class AttendanceRequestListPresenter extends BasePresenter
{
    /* ================================
        修正申請リストの生成
    ================================= */

    public static function make($requests, $tab)
    {
        return [
            'tab'       => $tab,
            'isPending' => ($tab === CorrectionRequest::STATUS_PENDING),
            'isApproved' => ($tab === CorrectionRequest::STATUS_APPROVED),
            'requests'  => self::formatRequests($requests),
        ];
    }

    /* ================================
        申請データ一覧の整形処理
    ================================= */

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

                'target_date'    => $req->attendance?->date?->format('Y/m/d') ?? '',

                'remarks'        => self::limit($req->remarks, 10),
                'remarks_full'   => $req->remarks,

                'request_date'   => $req->created_at?->format('Y/m/d') ?? '',
            ];
        });
    }
}

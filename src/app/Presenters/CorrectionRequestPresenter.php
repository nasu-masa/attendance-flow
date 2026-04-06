<?php

namespace App\Presenters;

class CorrectionRequestPresenter extends BasePresenter
{

    /* ================================
        修正申請の詳細データ作成
    ================================= */

    public static function make($req)
    {
        $attendance = $req->attendance;
        $after = $req->after_value ?? [];

        return [
            'id' => $req->id,

            'date_year'     => $attendance->date?->isoFormat('YYYY年'),
            'date_md'       => $attendance->date?->isoFormat('M月D日'),

            'clock_in'      => self::formatTime($after['clock_in'] ?? null),
            'clock_out'     => self::formatTime($after['clock_out'] ?? null),

            'break_start_1' => self::formatTime($after['break_start_1'] ?? null),
            'break_end_1'   => self::formatTime($after['break_end_1'] ?? null),

            'break_start_2' => self::formatTime($after['break_start_2'] ?? null),
            'break_end_2'   => self::formatTime($after['break_end_2'] ?? null),

            'remarks'       => $req->remarks ?? '',

            'is_pending'    => $req->isPending(),
        ];
    }
}

<?php

namespace App\Presenters;

class AdminDailyAttendanceListPresenter extends BasePresenter
{
    /* ================================
        日次勤怠リスト（管理者用）
    ================================= */

    public static function make(array $nav, $days)
    {
        return [
            'current_date'       => $nav['current']->format('Y年m月d日'),
            'current_date_slash' => $nav['current']->format('Y/m/d'),
            'prev_date'          => $nav['prev']->toDateString(),
            'next_date'          => $nav['next']->toDateString(),
            'attendances'        => $days
        ];
    }
}

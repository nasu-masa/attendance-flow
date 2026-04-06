<?php

namespace App\Presenters;

class AttendanceListPresenter extends BasePresenter
{
    /* ================================
        月次勤怠データの構築
    ================================= */

    public static function make(array $nav, $days)
    {
        return [
            'current_month' => $nav['current']->format('Y/m'),
            'prev_year'     => $nav['prev']->year,
            'prev_month'    => $nav['prev']->month,
            'next_year'     => $nav['next']->year,
            'next_month'    => $nav['next']->month,
            'days'          => self::formatDays($days),
        ];
    }

    /* ================================
        データの整形
    ================================= */

    protected static function formatDays($days)
    {
        return collect($days)->map(fn($attendance) => [
                'id'         => $attendance->id,
                'is_empty'   => $attendance->is_empty,

                'date_text'  => $attendance->date?->isoFormat('MM/DD(dd)') ?? '',

                'clock_in'   => self::resolveValue('clock_in', [], $attendance, 'H:i'),
                'clock_out'  => self::resolveValue('clock_out', [], $attendance, 'H:i'),

                'break_time' => (new AttendancePresenter($attendance))->breakTime(),
                'work_time'  => (new AttendancePresenter($attendance))->workTime(),
            ]);
    }
}

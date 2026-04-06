<?php

namespace App\Presenters;

use App\Models\Attendance;

class AttendancePresenter extends BasePresenter
{
    public function __construct(private Attendance $attendance)
    {}

    /* ================================
        勤怠状態判定
    ================================= */

    public function statusText(): string
    {
        $labels = config('attendance.status_labels');
        return $labels[$this->attendance->status] ?? $labels[Attendance::STATUS_OUT] ?? '';
    }

    /* ================================
        勤怠データの合計値整形
    ================================= */

    public function breakTime(): string
    {
        return $this->formatMinutes($this->attendance->total_break_minutes);
    }

    public function workTime(): string
    {
        return $this->formatMinutes($this->attendance->total_work_minutes);
    }
}

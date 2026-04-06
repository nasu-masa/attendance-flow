<?php

namespace App\Presenters;

use App\Models\Attendance;

class AttendanceDetailPresenter extends BasePresenter
{
    protected Attendance $attendance;
    protected array $after;
    protected $isAdmin;

    /* ================================
        申請中データの取得
    ================================= */

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->after = $attendance->latestCorrectionRequest?->after_value ?? [];
    }

    /* ================================
        インスタンス作成（管理者フラグの制御）
    ================================= */

    public static function make($attendance, $isAdmin = false)
    {
        $self = new self($attendance);
        $self->isAdmin = $isAdmin;
        return $self->toArray();
    }

    /* ================================
        表示用データの整形出力
    ================================= */

    public function toArray(): array
    {
        return [
            'id'        => $this->attendance->id,
            'user_name' => $this->attendance->user->name,

            'date_year' => $this->attendance->date?->isoFormat('YYYY年') ?? '',
            'date_md'   => $this->attendance->date?->isoFormat('M月D日') ?? '',

            'clock_in' => $this->resolveValue(
                'clock_in', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->clock_in, 'H:i'
            ),

            'clock_out' => $this->resolveValue(
                'clock_out', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->clock_out, 'H:i'
            ),

            'break_start_1' => $this->resolveValue(
                'break_start_1', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->break1?->break_start, 'H:i'
            ),

            'break_end_1' => $this->resolveValue(
                'break_end_1', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->break1?->break_end, 'H:i'
            ),

            'break_start_2' => $this->resolveValue(
                'break_start_2', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->break2?->break_start, 'H:i'
            ),

            'break_end_2' => $this->resolveValue(
                'break_end_2', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->break2?->break_end, 'H:i'
            ),

            'remarks' => $this->resolveValue(
                'remarks', $this->isAdmin
                    ? []
                    : $this->after, $this->attendance->remarks
            ),

            'is_pending'    => $this->attendance->isCorrectionPending(),
        ];
    }
}

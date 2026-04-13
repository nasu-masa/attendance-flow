<?php

namespace App\Presenters;

use App\Models\Attendance;

class AdminAttendanceDetailPresenter extends BasePresenter
{
    protected Attendance $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public static function make($attendance)
    {
        return (new self($attendance))->toArray();
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->attendance->id,
            'user_name' => $this->attendance->user->name,
            'date_year' => $this->attendance->date?->locale('ja')->isoFormat('YYYY年') ?? '',
            'date_md'   => $this->attendance->date?->locale('ja')->isoFormat('M月D日') ?? '',

            'clock_in'  => self::formatTime($this->attendance->clock_in),
            'clock_out' => self::formatTime($this->attendance->clock_out),
            'remarks'   => $this->attendance->remarks,

            'breaks' => $this->attendance->breakLogs->map(function ($break, $index) {
                    $oldStart = old('breaks.' . $index . '.start');
                    $oldEnd = old('breaks.' . $index . '.end');

                    $hasOld = ($oldStart !== null && $oldStart !== '') || ($oldEnd !== null && $oldEnd !== '');

                    if ($hasOld) {
                        return [
                            'start' => $oldStart,
                            'end'   => $oldEnd,
                        ];
                    }

                    return [
                        'start' => self::formatTime($break->break_start),
                        'end'   => self::formatTime($break->break_end),
                    ];
                })->values()->toArray(),

            'is_pending' => $this->attendance->isCorrectionPending(),
        ];
    }
}
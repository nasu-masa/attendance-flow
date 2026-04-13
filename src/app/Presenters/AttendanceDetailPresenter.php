<?php

namespace App\Presenters;

use App\Models\Attendance;

class AttendanceDetailPresenter extends BasePresenter
{
    protected Attendance $attendance;
    /**
     * 【理由】修正申請が存在する場合に after_value を優先的に参照できるよう、初期化時に保持している。
     * 【制約】attendance は latestCorrectionRequest をロード済みである必要がある。
     * 【注意】after_value が不正形式の場合、後続の resolveValue で意図しない値が返る可能性がある。
     */
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public static function make($attendance)
    {
        return (new self($attendance))->toArray();
    }

    /**
     * 【理由】修正申請の反映・未反映を統一的に扱うため、resolveValue を通して値を決定している。
     * 【制約】attendance の関連（user, breakLogs）がロード済みである前提で値を参照している。
     * 【注意】after_value の内容が不完全な場合、一部の項目が空文字になる可能性がある。
     */
    public function toArray(): array
    {
        $isPending = $this->attendance->isCorrectionPending();

        $after = $isPending ? ($this->attendance->latestCorrectionRequest?->after_value ?? []) : [];

        return [
            'id'        => $this->attendance->id,
            'user_name' => $this->attendance->user->name,

            'date_year' => $this->attendance->date?->locale('ja')->isoFormat('YYYY年') ?? '',
            'date_md'   => $this->attendance->date?->locale('ja')->isoFormat('M月D日') ?? '',

            'clock_in' => $this->resolveValue('clock_in', $after, $this->attendance, 'H:i'),
            'clock_out' => $this->resolveValue('clock_out', $after, $this->attendance, 'H:i'),
            'remarks' => $this->resolveValue('remarks', $after, $this->attendance),

            'breaks' => $this->attendance->breakLogs->map(function ($break, $index) use ($isPending, $after) {
                $oldStart = old('breaks.' . $index . '.start');
                $oldEnd = old('breaks.' . $index . '.end');

                $hasOld = ($oldStart !== null && $oldStart !== '') || ($oldEnd !== null && $oldEnd !== '');

                if ($hasOld) {
                    return [
                        'start' => $oldStart,
                        'end'   => $oldEnd,
                    ];
                }

                if ($isPending && isset($after['breaks'][$index])) {
                    return [
                        'start' => self::formatTime($after['breaks'][$index]['start']),
                        'end'   => self::formatTime($after['breaks'][$index]['end']),
                    ];
                }

                return [
                    'start' => self::formatTime($break->break_start),
                    'end'   => self::formatTime($break->break_end),
                ];
            })->values()->toArray(),

            'is_pending' => $isPending,
        ];
    }
}

<?php

namespace App\Presenters;

use App\Models\Attendance;

class AttendanceDetailPresenter extends BasePresenter
{
    protected Attendance $attendance;
    protected array $after;
    protected $isAdmin;

    /**
     * 【理由】修正申請が存在する場合に after_value を優先的に参照できるよう、初期化時に保持している。
     * 【制約】attendance は latestCorrectionRequest をロード済みである必要がある。
     * 【注意】after_value が不正形式の場合、後続の resolveValue で意図しない値が返る可能性がある。
     */
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->after = $attendance->latestCorrectionRequest?->after_value ?? [];
    }

    /**
     * 【理由】管理者・一般ユーザーで表示仕様を切り替えるため、isAdmin を外部から指定できるようにしている。
     * 【制約】attendance は toArray() が期待する関連データをロード済みである必要がある。
     * 【注意】isAdmin の値により after_value の適用有無が変わるため、呼び出し側は意図を明確にする必要がある。
     */
    public static function make($attendance, $isAdmin = false)
    {
        $self = new self($attendance);
        $self->isAdmin = $isAdmin;
        return $self->toArray();
    }

    /**
     * 【理由】修正申請の反映・未反映を統一的に扱うため、resolveValue を通して値を決定している。
     * 【制約】attendance の関連（user, break1, break2）がロード済みである前提で値を参照している。
     * 【注意】after_value の内容が不完全な場合、一部の項目が空文字になる可能性がある。
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->attendance->id,
            'user_name' => $this->attendance->user->name,

            'date_year' => $this->attendance->date?->locale('ja')->isoFormat('YYYY年') ?? '',
            'date_md'   => $this->attendance->date?->locale('ja')->isoFormat('M月D日') ?? '',

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

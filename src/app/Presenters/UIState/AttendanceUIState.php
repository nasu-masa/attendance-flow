<?php

namespace App\Presenters\UIState;

use App\Models\Attendance;

class AttendanceUIState
{
    public function __construct(private ?Attendance $attendance) {}

    /* =====================================
        状態判定（現在の勤務ステータスを確認）
    ===================================== */

    /**
     * 勤務外（出勤前）かどうか
     */
    public function isOut(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_OUT || is_null($this->attendance);
    }

    /**
     * 出勤中かどうか
     */
    public function isWorking(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_WORKING;
    }

    /**
     * 休憩中かどうか
     */
    public function isBreak(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_BREAK;
    }

    /**
     * 退勤済（一日の業務終了）かどうか
     */
    public function isFinished(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_FINISHED;
    }

    /* ============================================
        アクション定義
    ============================================ */

    /**
     * 出勤開始アクション名
     */
    public function startAction(): string
    {
        return Attendance::ACTION_START;
    }

    /**
     * 休憩開始アクション名
     */
    public function breakInAction(): string
    {
        return Attendance::ACTION_BREAK_IN;
    }

    /**
     * 休憩終了アクション名
     */
    public function breakOutAction(): string
    {
        return Attendance::ACTION_BREAK_OUT;
    }

    /**
     * 退勤終了アクション名
     */
    public function finishAction(): string
    {
        return Attendance::ACTION_FINISH;
    }
}

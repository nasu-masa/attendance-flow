<?php

namespace App\Presenters\UIState;

use App\Models\Attendance;

class AttendanceUIState
{
    /**
     * 【理由】ビュー側でステータス値や null 判定を直接行わせず、UI 判定ロジックを集約するためのメソッド群。
     * 【制約】$attendance は null または有効な Attendance インスタンスである必要がある。
     * 【注意】attendance が null の場合は OUT とみなす UI 仕様を採用しているため、
     *          初回打刻前の画面表示は OUT と同等になる。
     */
    public function __construct(private ?Attendance $attendance)
    {}

    public function isOut(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_OUT || is_null($this->attendance);
    }

    public function isWorking(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_WORKING;
    }

    public function isBreak(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_BREAK;
    }

    public function isFinished(): bool
    {
        return $this->attendance?->status === Attendance::STATUS_FINISHED;
    }

    /**
     * 【理由】UI の状態に応じて使用するナビを 1 箇所で決定し、表示側の分岐を排除するため。
     * 【制約】返却するパスは既存 partial 名と一致していることを前提とする。
     * 【注意】partial のファイル名や配置変更があった場合はこのメソッドも更新が必要になる。
     */
    public function navView()
    {
        return $this->isFinished()
            ? 'partials.finished-nav'
            : 'partials.default-nav';
    }

    /**
     * 【理由】ビュー側でアクション名をハードコーディングしないため、Attendance モデルの定数を返す役割を持つメソッド群。
     * 【制約】Attendance モデルに対応するアクション定数（ACTION_START など）が正しく定義されている必要がある。
     * 【注意】アクション名を変更する場合は、モデル側の定数と本メソッド群を合わせて更新すること。
     */
    public function startAction(): string
    {
        return Attendance::ACTION_START;
    }

    public function breakInAction(): string
    {
        return Attendance::ACTION_BREAK_IN;
    }

    public function breakOutAction(): string
    {
        return Attendance::ACTION_BREAK_OUT;
    }

    public function finishAction(): string
    {
        return Attendance::ACTION_FINISH;
    }
}

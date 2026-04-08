<?php

namespace App\Presenters;

use App\Models\Attendance;

class AttendancePresenter extends BasePresenter
{
    /**
     * 【理由】勤怠データを受け取り、ステータスラベルや時間表示の整形を行う Presenter として利用するため。
     * 【制約】$attendance は有効な Attendance インスタンスである必要がある。
     * 【注意】Presenter は表示専用であり、更新処理は行わない。
     */
    public function __construct(private Attendance $attendance)
    {}

    /**
     * 【理由】勤怠ステータスを表示用ラベルに変換し、ビュー側のロジックを排除するため。
     * 【制約】config('attendance.status_labels') に対応するキーが存在する必要がある。
     * 【注意】ラベルが未定義の場合は OUT をデフォルトとして扱う。
     */
    public function statusText(): string
    {
        $labels = config('attendance.status_labels');
        return $labels[$this->attendance->status] ?? $labels[Attendance::STATUS_OUT] ?? '';
    }

    /**
     * 【理由】日付表示の仕様を一元化し、UI と JS のフォーマット差異を防ぐため。
     * 【制約】日本語ロケールを前提とした曜日表記を使用するため、他ロケールでは意図通りにならない。
     * 【注意】フォーマット変更時は全画面の表示仕様が一括で変わるため、影響範囲が広い。
     */
    public function dateFormat(): string
    {
        return 'YYYY年M月D日(ddd)';
    }

    /**
     * 【理由】UI 要件として秒を含めない時刻表示を固定し、表示揺れを防ぐため。
     * 【制約】24時間表記を前提としており、AM/PM を使うロケールでは適合しない。
     * 【注意】秒を必要とする機能追加が発生した場合、このメソッドの変更が必須になる。
     */
    public function timeFormat(): string
    {
        return 'HH:mm';
    }

    /**
     * 【理由】休憩時間（分）を UI 表示に適したフォーマットへ変換するため。
     * 【制約】total_break_minutes が整数である必要がある。
     * 【注意】フォーマット仕様を変更する場合は formatMinutes の実装も合わせて変更する。
     */
    public function breakTime(): string
    {
        return $this->formatMinutes($this->attendance->total_break_minutes);
    }

    /**
     * 【理由】勤務時間（分）を UI 表示に適したフォーマットへ変換するため。
     * 【制約】total_work_minutes が整数である必要がある。
     * 【注意】休憩時間と同様、フォーマット仕様は formatMinutes に依存する。
     */
    public function workTime(): string
    {
        return $this->formatMinutes($this->attendance->total_work_minutes);
    }
}

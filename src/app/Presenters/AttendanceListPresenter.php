<?php

namespace App\Presenters;

class AttendanceListPresenter extends BasePresenter
{
    /**
     * 【理由】月ナビゲーションと日別データを統合し、ビューが扱いやすい一覧構造へ変換するため。
     * 【制約】nav 配列に必要なキー（current・prev・next）が揃っていることを前提とする。
     * 【注意】days の形式が崩れると一覧生成が破綻するため、呼び出し側で整形済みデータを渡す必要がある。
     */
    public static function make(array $nav, array $days)
    {
        return [
            'current_month' => $nav['current']->locale('ja')->isoFormat('YYYY/MM'),
            'prev_year'     => $nav['prev']->year,
            'prev_month'    => $nav['prev']->month,
            'next_year'     => $nav['next']->year,
            'next_month'    => $nav['next']->month,
            'days'          => self::formatDays($days),
        ];
    }

    /**
     * 【理由】日別勤怠データを UI 表示に最適化した配列へ変換し、ビューの依存を最小化するため。
     * 【制約】attendance が最低限の属性（date・is_empty など）を保持している必要がある。
     * 【注意】空日の場合でも全キーを返すため、実データとの区別は呼び出し側で判断する必要がある。
     */
    protected static function formatDays(array $days)
    {
        return collect($days)->map(fn($attendance) => [
                'id'         => $attendance->id,
                'is_empty'   => $attendance->is_empty,

                'date_text'  => $attendance->date?->locale('ja')->isoFormat('MM/DD(dd)') ?? '',

                'clock_in'   => self::resolveValue('clock_in', [], $attendance, 'H:i'),
                'clock_out'  => self::resolveValue('clock_out', [], $attendance, 'H:i'),

                'break_time' => (new AttendancePresenter($attendance))->breakTime(),
                'work_time'  => (new AttendancePresenter($attendance))->workTime(),
            ]);
    }
}

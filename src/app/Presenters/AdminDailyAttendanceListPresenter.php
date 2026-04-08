<?php

namespace App\Presenters;

class AdminDailyAttendanceListPresenter extends BasePresenter
{
    /**
     * 【理由】日付フォーマットをここで統一し、view 側が複数形式を意識せずに済むようにするため。
     * 【制約】nav 配列には Carbon インスタンスが格納されている前提でフォーマット処理を行う。
     * 【注意】表示形式を変更する場合、このメソッドが影響範囲となるため UI 仕様変更時は要確認。
     */
    public static function make(array $nav, $days)
    {
        return [
            'current_date'       => $nav['current']->locale('ja')->isoFormat('YYYY年M月D日'),
            'current_date_slash' => $nav['current']->locale('ja')->isoFormat('YYYY/MM/DD'),
            'prev_date'          => $nav['prev']->locale('ja')->isoFormat('YYYY/MM/DD'),
            'next_date'          => $nav['next']->locale('ja')->isoFormat('YYYY/MM/DD'),
            'attendances'        => $days
        ];
    }
}

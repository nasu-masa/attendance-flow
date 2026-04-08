<?php

namespace App\Support\Url;

use Carbon\Carbon;

class CsvExportUrl
{
    /**
     * 【理由】CSV エクスポート用 URL の生成を一元化し、Controller や Presenter の責務を分離するため。
     * 【制約】$current は Carbon インスタンスであり、year と month が取得できる前提で動作する。
     * 【注意】route 名やパラメータ構造が変わった場合は、このメソッドを更新しないと全体が破綻する。
     */
    public static function make(int $userId, Carbon $current): string
    {
        return route('admin.attendance.staff.csv', [
            'id'    => $userId,
            'year'  => $current->year,
            'month' => $current->month,
        ]);
    }
}

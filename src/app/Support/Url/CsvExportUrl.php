<?php

namespace App\Support\Url;

class CsvExportUrl
{

    /* ================================
        Export CSV URLの生成
    ================================= */
    public static function make($userId, $current)
    {
        return route('admin.attendance.staff.csv', [
            'id'    => $userId,
            'year'  => $current->year,
            'month' => $current->month,
        ]);
    }
}

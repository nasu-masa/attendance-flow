<?php

use App\Models\Attendance;
use App\Models\CorrectionRequest;

/* ================================
    ステータスの表示ラベル
================================= */

return [
    'status_labels' => [
        Attendance::STATUS_OUT      => '勤務外',
        Attendance::STATUS_WORKING  => '出勤中',
        Attendance::STATUS_BREAK    => '休憩中',
        Attendance::STATUS_FINISHED => '退勤済',
    ],

    'request_status_labels' => [
        CorrectionRequest::STATUS_PENDING  => '承認待ち',
        CorrectionRequest::STATUS_APPROVED => '承認済み',
    ],
];
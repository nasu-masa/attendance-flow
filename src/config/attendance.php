<?php

use App\Models\Attendance;
use App\Models\CorrectionRequest;

/**
 * 【理由】勤怠・申請ステータスの日本語ラベルを一元管理し、文言変更の影響範囲を最小化するため。
 * 【制約】モデル側のステータス定数と同期している前提で動作し、値の追加・変更はここにも反映が必要。
 * 【注意】業務仕様に依存する文言のため、翻訳ファイルではなく config で管理している。
 */

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
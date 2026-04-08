<?php

namespace App\Presenters;

use App\Models\CorrectionRequest;

class CorrectionRequestPresenter extends BasePresenter
{
    /**
     * 【理由】修正申請の after_value を優先して整形し、承認画面で必要な情報を統一フォーマットで提供するため。
     * 【制約】$req は attendance リレーションを持つ CorrectionRequest インスタンスである必要がある。
     * 【注意】after_value の欠損や不正値は空文字として扱われるため、元データとの整合性は呼び出し側に依存する。
     */
    public static function make(CorrectionRequest $req)
    {
        $attendance = $req->attendance;
        $after = $req->after_value ?? [];

        return [
            'id' => $req->id,

            'date_year'     => $attendance->date?->locale('ja')->isoFormat('YYYY年'),
            'date_md'       => $attendance->date?->locale('ja')->isoFormat('M月D日'),

            'clock_in'      => self::formatTime($after['clock_in'] ?? null),
            'clock_out'     => self::formatTime($after['clock_out'] ?? null),

            'break_start_1' => self::formatTime($after['break_start_1'] ?? null),
            'break_end_1'   => self::formatTime($after['break_end_1'] ?? null),

            'break_start_2' => self::formatTime($after['break_start_2'] ?? null),
            'break_end_2'   => self::formatTime($after['break_end_2'] ?? null),

            'remarks'       => $req->remarks ?? '',

            'is_pending'    => $req->isPending(),
        ];
    }
}

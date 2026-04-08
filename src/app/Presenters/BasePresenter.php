<?php

namespace App\Presenters;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

abstract class BasePresenter
{

    public static function formatMinutes(?int $minutes): string
    {
        if ($minutes === null || $minutes <= 0) return '';

        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * 【理由】文字列・Carbon の両方を受け取り、入力形式の揺れを吸収して統一した時刻フォーマット（H:i）を提供するため。
     * 【制約】$value は時刻として解釈可能な値であることが望ましいが、null や空文字も許容される。
     * 【注意】パースに失敗した場合は例外を投げず空文字を返すため、不正値の検知は呼び出し側では行われない。
     */

    public static function formatTime(null|string|Carbon $value): string
    {
        if (!$value) return '';
        try {
            return $value instanceof Carbon
                ? $value->format('H:i')
                : Carbon::parse($value)->format('H:i');
        } catch (\Exception) {
            return '';
        }
    }

    /**
     * 【理由】old入力・修正申請・元データの優先順位を統一的に処理するための共通ロジックとして実装している。
     * 【制約】$attendance は Model / Carbon / 配列のいずれかである必要があり、その他の型は未定義動作となる。
     * 【注意】$format が 'H:i' の場合は時刻変換が行われるため、不正な値が渡ると空文字や誤変換が発生する可能性がある。
     */
    public static function resolveValue(string $key, array $after, $attendance, ?string $format = null): string
    {
        $oldValue = old($key);
        if (!is_null($oldValue) && $oldValue !== '') {
            return (string)$oldValue;
        }

        if (isset($after[$key])) {
            $value = $after[$key];
        } else {
            if ($attendance instanceof Model) {
                $value = $attendance->getLatestEffectiveAttribute($key);
            } elseif ($attendance instanceof Carbon || $attendance instanceof DateTimeInterface) {
                $value = $attendance;
            } else {
                $value = is_array($attendance) ? ($attendance[$key] ?? null) : $attendance;
            }
        }

        if ($format === 'H:i') return self::formatTime($value);

        return (string)($value ?? '');
    }

    /**
     * 【理由】一覧表示で文字列が長すぎる場合のレイアウト崩れを防ぐため、統一した長さに短縮する目的がある。
     * 【制約】$value が null の場合は空文字として扱う前提で Str::limit が利用される。
     * 【注意】短縮後の全文表示は別フィールドで保持する設計前提のため、ここでは省略値のみ返す点に注意。
     */
    public static function limit(?string $value, int $limit = 10): string
    {
        return Str::limit($value ?? '', $limit);
    }
}
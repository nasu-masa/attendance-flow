<?php

namespace App\Presenters;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

abstract class BasePresenter
{
    /* ================================
        合計時間の整形（分 → H:mm）
    ================================= */

    public static function formatMinutes(?int $minutes): string
    {
        if ($minutes === null || $minutes <= 0) return '';

        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /* ================================
        時刻の整形（Carbon/文字列 → H:i）
    ================================= */

    public static function formatTime($value): string
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

    /* ================================
        表示値の解決（優先順位の制御）
        old値 > 申請中の値 > 最新の確定値
    ================================= */

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

    /* ================================
        文字列の省略（レイアウト崩れ防止）
    ================================= */

    public static function limit(?string $value, int $limit = 10): string
    {
        return Str::limit($value ?? '', $limit);
    }
}
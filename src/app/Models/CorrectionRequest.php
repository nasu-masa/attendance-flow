<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_type',
        'before_value',
        'after_value',
        'status',
        'remarks',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'before_value' => 'array',
        'after_value'  => 'array',
        'approved_at'  => 'datetime'
    ];

    /* ================================
        Relations
    ================================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /* ================================
        Scopes
    ================================= */

    /**
     * 【理由】pending 状態の申請のみを抽出し、一覧や集計の基準を統一するため。
     * 【制約】status が pending と approved の2種類のみで管理されている前提で動作する。
     * 【注意】pending 以外の状態は存在しないため、複雑な遷移は考慮していない。
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * 【理由】承認済みの申請だけを抽出し、履歴表示や集計に利用するため。
     * 【制約】status が pending と approved の2種類のみで、一意に承認状態が決まる前提。
     * 【注意】承認後の取消や却下などの別状態は存在しない前提で設計されている。
     */
    public function scopeApproved(Builder $query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /* ================================
        Business Logic
    ================================= */

    /**
     * 【理由】インスタンスが pending 状態かどうかを簡潔に判定するため。
     * 【制約】status が pending と approved の2種類のみで、pending の定義が固定されている。
     * 【注意】他の状態は存在しないため、単純比較での判定に依存している。
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 【理由】承認済みかどうかをインスタンス単位で判定し、処理分岐を簡潔にするため。
     * 【制約】status が pending と approved の2種類のみで、approved が唯一の承認状態である前提。
     * 【注意】取消や却下などの別状態は存在しないため、単純比較での判定に依存する。
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * 【理由】before と after の値を比較し、変更された項目のみを抽出するための基盤ロジックを提供する。
     * 【制約】引数が省略された場合はモデル自身の before_value・after_value を比較対象とする前提がある。
     * 【注意】null と空文字の違いも変更として扱うため、呼び出し側は値の正規化に注意が必要。
     */
    public function diff(?array $before = null, ?array $after = null): array
    {
        $before = $before ?? ($this->before_value ?? []);
        $after  = $after  ?? ($this->after_value  ?? []);

        return array_filter(
            $after,
            fn($value, $key) => ($before[$key] ?? null) !== $value,
            ARRAY_FILTER_USE_BOTH
        );
    }
}

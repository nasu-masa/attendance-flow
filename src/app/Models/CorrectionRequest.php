<?php

namespace App\Models;

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

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /* ================================
        Business Logic
    ================================= */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

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

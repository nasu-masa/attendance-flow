<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    public const STATUS_OUT      = 'out';
    public const STATUS_WORKING  = 'working';
    public const STATUS_BREAK    = 'break';
    public const STATUS_FINISHED = 'finished';

    public const ACTION_START     = 'start';
    public const ACTION_BREAK_IN  = 'break_in';
    public const ACTION_BREAK_OUT = 'break_out';
    public const ACTION_FINISH    = 'finish';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'remarks'
    ];

    protected $casts = [
        'date'       => 'date',
        'clock_in'   => 'datetime',
        'clock_out'  => 'datetime',
        'is_holiday' => 'boolean',
        'is_absent'  => 'boolean'
    ];

    /* ================================
        Relations
    ================================ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakLogs()
    {
        return $this->hasMany(BreakLog::class);
    }

    public function break1()
    {
        return $this->hasOne(BreakLog::class)->orderBy('break_start')->limit(1);
    }

    public function break2()
    {
        return $this->hasOne(BreakLog::class)->orderBy('break_start')->skip(1)->limit(1);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function latestCorrectionRequest()
    {
        return $this->hasOne(CorrectionRequest::class)->latestOfMany()
            ->orderByRaw("status = ? desc", [CorrectionRequest::STATUS_PENDING])
            ->latest();
    }

    /* ================================
        Accessors
    ================================ */

    public function getBreak1Attribute()
    {
        return $this->breakLogs->get(0);
    }

    public function getBreak2Attribute()
    {
        return $this->breakLogs->get(1);
    }

    public function getIsHolidayAttribute()
    {
        return (bool)($this->attributes['is_holiday'] ?? false);
    }

    public function getIsAbsentAttribute()
    {
        return (bool)($this->attributes['is_absent'] ?? false);
    }

    /* ================================
        Scopes
    ================================ */

    public function scopeOfMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeWithAllRelations($query)
    {
        return $query->with(
            'breakLogs',
            'user',
            'correctionRequests'
        );
    }

    public function scopeWithRelationsForDetails($query)
    {
        return $query->with([
            'user',
            'break1',
            'break2',
            'latestCorrectionRequest',
        ]);
    }

    /* ================================
        Business Logic
    ================================ */

    public function isCorrectionPending()
    {
        return $this->latestCorrectionRequest?->isPending() ?? false;
    }

    public function isAfternoonWork(): bool
    {
        return $this->clock_in && $this->clock_in->gte(Carbon::parse('12:00'));
    }

    /**
     * 承認済みの修正値があればそれを、なければ現在の値を返す
     */
    public function getLatestEffectiveAttribute($key)
    {
        $request = $this->latestCorrectionRequest;

        if ($request?->isApproved() && isset($request->after_value[$key])) {
            return $request->after_value[$key];
        }

        return $this->{$key};
    }

    public function getTotalBreakMinutesAttribute()
    {
        if ($this->is_holiday || $this->is_absent) return null;

        return $this->breakLogs->sum(function ($break) {
            return $break->break_end
                ? $break->break_start->diffInMinutes($break->break_end)
                : 0;
        });
    }

    public function getTotalWorkMinutesAttribute()
    {
        if ($this->is_holiday || $this->is_absent) return null;

        $in = $this->getLatestEffectiveAttribute('clock_in');
        $out = $this->getLatestEffectiveAttribute('clock_out');

        if (!$in || !$out) return null;

        $inObject = $in instanceof Carbon ? $in : Carbon::parse($in);
        $outObject = $out instanceof Carbon ? $out : Carbon::parse($out);

        return $inObject->diffInMinutes($outObject) - $this->total_break_minutes;
    }
}

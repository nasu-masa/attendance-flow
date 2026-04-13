<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'clock_in'   => 'string',
        'clock_out'  => 'string',
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
        return $this->hasMany(BreakLog::class)->orderBy('break_start');
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    /**
     * 【理由】複数の修正申請がある前提で、業務的に最も優先すべき1件（pending優先）を取得するため。
     * 【制約】pending が複数存在しないことを前提に、ステータスと作成日時で優先順位を決定している。
     * 【注意】並び順の制御に依存するため、DB 側のデータ不整合があると意図しない申請が選ばれる可能性がある。
     */
    public function latestCorrectionRequest()
    {
        return $this->hasOne(CorrectionRequest::class)->latestOfMany()
            ->orderByRaw("status = ? desc", [CorrectionRequest::STATUS_PENDING])
            ->latest();
    }

    /* ================================
        Accessors
    ================================ */
    /**
     * 【理由】null を含む値を boolean に正規化し、判定の一貫性を保つため。
     * 【制約】is_holiday が存在しないレコードを許容する前提で false をデフォルトとする。
     * 【注意】型変換は accessor 側で行われるため、呼び出し側は boolean として扱う必要がある。
     */
    public function getIsHolidayAttribute()
    {
        return (bool)($this->attributes['is_holiday'] ?? false);
    }

    /**
     * 【理由】欠勤フラグの null を boolean に正規化し、判定の揺れを防ぐため。
     * 【制約】is_absent が未設定のレコードを正常系として扱う前提で false を返す。
     * 【注意】型保証を accessor 側で行うため、呼び出し側は boolean として扱うことを前提とする。
     */
    public function getIsAbsentAttribute()
    {
        return (bool)($this->attributes['is_absent'] ?? false);
    }

    /* ================================
        Scopes
    ================================ */

    /**
     * 【理由】月単位の集計や一覧表示を行うため、年と月を明示的に絞り込む。
     * 【制約】date カラムが日付型であり、whereYear/whereMonth が正しく動作する前提。
     * 【注意】月を跨ぐデータは含まれないため、期間指定の柔軟性は持たない。
     */
    public function scopeOfMonth(Builder $query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    /**
     * 【理由】勤怠画面で必要な関連データを一括ロードし、N+1 を防ぐため。
     * 【制約】breakLogs・user・correctionRequests が常に必要になる前提で eager load を行う。
     * 【注意】関連が欠損していても空コレクションとして扱われ、例外にはならない。
     */
    public function scopeWithAllRelations(Builder $query)
    {
        return $query->with(
            'breakLogs',
            'user',
            'correctionRequests'
        );
    }

    /**
     * 【理由】詳細画面で必要となる関連を事前ロードし、表示時の追加クエリを防ぐため。
     * 【制約】関連名（user, breakLogs, latestCorrectionRequest）が正しく定義されている必要がある。
     * 【注意】関連が存在しない場合でも null が返るため、Presenter 側で null 安全に扱う必要がある。
     */
    public function scopeWithRelationsForDetails(Builder $query)
    {
        return $query->with([
            'user',
            'breakLogs',
            'latestCorrectionRequest',
        ]);
    }

    /* ================================
        Business Logic
    ================================ */

    /**
     * 【理由】最新の修正申請の状態に基づいて UI や処理分岐を簡潔に判定するため。
     * 【制約】最新申請が1件だけ存在し、その状態が正しく更新されている前提で動作する。
     * 【注意】申請が存在しない場合は未申請扱いとなり、例外ではなく false を返す。
     */
    public function isCorrectionPending()
    {
        return $this->latestCorrectionRequest?->isPending() ?? false;
    }

    /**
     * 【理由】出勤時刻が午後に該当するかどうかを固定基準（12:00）で判定するため。
     * 【制約】clock_in が有効な日時であることを前提として比較を行う。
     * 【注意】日付跨ぎやタイムゾーン差異は考慮せず、単純な時刻比較に依存する。
     */
    public function isAfternoonWork(): bool
    {
        return $this->clock_in && $this->clock_in->gte(Carbon::parse('12:00'));
    }


    /**
     * 【理由】承認済みの修正申請がある場合に修正後の値を優先し、表示と計算の基準を統一するため。
     * 【制約】after_value に対象キーが存在することを前提としており、欠損時は元の値に戻る。
     * 【注意】複数の修正申請がある場合でも最新1件のみを参照し、整合性の保証は行わない。
     */
    public function getLatestEffectiveAttribute($key)
    {
        $request = $this->latestCorrectionRequest;

        if ($request?->isApproved() && isset($request->after_value[$key])) {
            return $request->after_value[$key];
        }

        return $this->{$key};
    }

    /**
     * 【理由】複数の休憩ログを集約し、勤務時間計算に必要な総休憩時間を算出するため。
     * 【制約】休暇・欠勤日は休憩概念が成立しない前提で null を返す。
     * 【注意】break_end が未設定のログは0分扱いとなり、不整合の補正は行わない。
     */
    public function getTotalBreakMinutesAttribute()
    {
        if ($this->is_holiday || $this->is_absent) return null;

        return $this->breakLogs->sum(function ($break) {
            return $break->break_end
                ? $break->break_start->diffInMinutes($break->break_end)
                : 0;
        });
    }

    /**
     * 【理由】修正後の値を含む最終的な出退勤時刻を基準に勤務時間を算出するため。
     * 【制約】出勤・退勤の両方が揃っている前提で計算し、不完全データは null を返す。
     * 【注意】休憩時間の異常（未終了など）は補正されず、そのまま計算結果に反映される。
     */
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

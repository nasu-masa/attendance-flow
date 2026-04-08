<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * ネーミングコンフリクトを避けコードの可読性と正確性を保つため
 *  Illuminate\Foundation\Auth\UserをAuthenticatableとしてインポート
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER  = 'user';
    public const ROLE_STAFF = 'staff';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* ================================
        Relations
    ================================= */

    public function Attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    /* ================================
        Role Checks
    ================================= */

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff()
    {
        return $this->role === self::ROLE_STAFF;
    }

    /* ================================
        Scopes
    ================================= */

    public function scopeStaff($query)
    {
        return $query->where('role', self::ROLE_STAFF);
    }

    /* ================================
        Email Verification
    ================================= */

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }
}

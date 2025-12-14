<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;

class Staff extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;

    protected $table = 'staffs';

    protected $fillable = [
        'role',
        'name',
        'email',
        'password'
    ];

    // リレーションの定義
    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approved_by');
    }

    // メール認証イベントの発火
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    // 管理者に適用する処理
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // 管理者は除く処理
    public function scopeNotAdmin($query)
    {
        return $query->where('role', '!=', 'admin');
    }

    // 管理者をメール認証の対象から外す
    public function hasVerifiedEmail()
    {
        if ($this->isAdmin()) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }
}

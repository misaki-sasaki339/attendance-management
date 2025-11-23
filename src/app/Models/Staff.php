<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Database\Factories\StaffFactory;


class Staff extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

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
        static $sent = false;
        if ($sent) {
            return;
        }
        $sent = true;
        $this->notify(new VerifyEmailNotification());
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}

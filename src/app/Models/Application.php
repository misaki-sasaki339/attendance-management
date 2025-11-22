<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'new_clock_in',
        'new_clock_out',
        'new_break_times',
        'reason',
        'status',
    ];

    protected $casts = [
        'new_clock_in' => 'datetime',
        'new_clock_out' => 'datetime',
        'new_break_times' => 'array',
    ];

    // リレーションの定義
    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function approval()
    {
        return $this->hasOne(Approval::class);
    }

    // 修正申請の承認状態を導出
    public function getStatusAttribute(): string
    {
        return $this->approval ? '承認済み' : '承認待ち';
    }
}

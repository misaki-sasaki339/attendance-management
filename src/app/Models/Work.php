<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'work_date',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime'
    ];

    // リレーション定義
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function application()
    {
        return $this->hasOne(Application::class);
    }

    // 勤務状態の導出
    public function getStatusAttribute()
    {
        // 勤務外の判定
        if (is_null($this->clock_in)) {
            return '勤務外';
        }

        // 休憩中の判定
        if ($this->breakTimes()->whereNull('break_end')->exists()) {
            return '休憩中';
        }

        // 出勤中の判定
        if (is_null($this->clock_out)) {
            return '出勤中';
        }

        return '退勤済';
    }

    // 休憩時間の計算
    private function calculateBreakMinutes(): int
    {
        return $this->BreakTimes->sum(fn($break) =>
            $break->break_end && $break->break_start
                ? $break->break_end->diffInMinutes($break->break_start)
                : 0
        );
    }

    // 休憩時間の取得
    public function getTotalBreakTimeAttribute(): string
    {
        $minutes = $this->calculateBreakMinutes();
        return sprintf('%02d:%02d', floor($minutes / 60), $minutes/ 60);
    }

    // 勤務時間の計算
    public function getWorkingHoursAttribute(): ?string
    {
        // 出勤または退勤していない場合はnull
        if (is_null($this->clock_in) || is_null($this->clock_out)) {
            return null;
        }

        // 1日の総労働時間
        $totalMinutes = $this->clock_out->diffInMinutes($this->clock_in);

        // 休憩分を減算
        $breakMinutes = $this->calculateBreakMinutes();

        // 実労働時間の計算
        $workMinutes = max($totalMinutes - $breakMinutes, 0);
        return sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
    }

    // 打刻機能
    // 出勤打刻
    public function clockIn(){
        $this->clock_in = now();
        $this->save();
    }

    // 退勤打刻
    public function clockOut(){
        $this->clock_out = now();
        $this->save();
    }

    // 当日の勤怠データを取得
    public static function todayWork()
    {
        return static::where('staff_id', Auth::id())
            ->whereDate('work_date', today())
            ->with('breakTimes')
            ->firstOrFail();
    }




}

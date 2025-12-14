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
        'reason',
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

    // 休憩時間の計算(分)
    private function calculateBreakMinutes(): int
    {
        return $this->breakTimes->sum(function ($break) {
            if ($break->break_start && $break->break_end) {
                return $break->break_start->diffInMinutes($break->break_end, true);
            }
            return 0;
        });
    }

    // 休憩合計時間(HH:MM)
    public function getBreakTimeAttribute(): string
    {
        $minutes = $this->calculateBreakMinutes();
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    // 勤務時間の計算(HH:MM)
    public function getWorkTimeAttribute(): ?string
    {
        // 出勤または退勤していない場合はnull
        if (is_null($this->clock_in) || is_null($this->clock_out)) {
            return null;
        }

        // 1日の総労働時間
        $total = $this->clock_in->diffInMinutes($this->clock_out, true);

        // 休憩分を減算
        $break = $this->calculateBreakMinutes();

        // 実労働時間の計算
        $work = max($total - $break, 0);
        $hours = intdiv($work, 60);
        $mins = $work % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    // 打刻機能
    // 出勤打刻
    public function clockIn()
    {
        $this->clock_in = now();
        $this->save();
    }

    // 退勤打刻
    public function clockOut()
    {
        $this->clock_out = now();
        $this->save();
    }

    // 当日の勤怠データを取得
    public static function todayWork()
    {
        $work = static::where('staff_id', Auth::id())
            ->whereDate('work_date', today())
            ->with('breakTimes')
            ->first();

        if (!$work) {
            $work = static::create([
                'staff_id' => Auth::id(),
                'work_date' => today(),
                'clock_in' => null,
                'clock_out' => null,
            ]);
        }
        return $work;
    }

    // 申請があるかの判定
    public function hasApplication()
    {
        return $this->application !== null;
    }

    // 申請が承認待ちかの判定
    public function isPending()
    {
        // approvalレコードが「存在しない」＝承認待ち
        return $this->application && $this->application->approval === null;
    }

    // 申請が承認済みかの判定
    public function isApproved()
    {
        // approvalレコードが「存在する」＝承認済み
        return $this->application && $this->application->approval !== null;
    }
}

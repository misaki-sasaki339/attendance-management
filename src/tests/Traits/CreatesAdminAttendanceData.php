<?php

namespace Tests\Traits;

use App\Models\Staff;
use App\Models\BreakTime;
use App\Models\Work;
use Carbon\Carbon;

trait CreatesAdminAttendanceData
{
    protected $admin;
    protected $staffA;
    protected $staffB;
    protected $workA;
    protected $workB;


    // 管理者の作成
    protected function createAdmin(): void
    {
        if (!$this->admin) {
            $this->admin = Staff::factory()->create([
                'role' => 'admin',
                'name' => '管理者',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
            ]);
        }
    }

    // その日の勤怠データを2人分作成
    protected function createAttendanceForDate(
        Carbon $date,
        array $overridesA = [],
        array $overridesB = []
    ): void
    {
        if (!$this->staffA) {
            $this->staffA = Staff::factory()->create();
        }

        if (!$this->staffB) {
            $this->staffB = Staff::factory()->create();
        }

        // スタッフAの勤怠データ作成
        $this->workA = Work::factory()->create(array_merge([
            'staff_id' => $this->staffA->id,
            'work_date' => $date->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'テストテストテスト'
        ], $overridesA));

        // スタッフAの勤怠に基づく休憩データ作成
        BreakTime::factory()->create([
            'work_id' => $this->workA->id,
            'break_start' => $date->copy()->setTime(12, 0),
            'break_end' => $date->copy()->setTime(13, 0),
        ]);

        // スタッフBの勤怠データ作成
        $this->workB = Work::factory()->create(array_merge([
            'staff_id' => $this->staffB->id,
            'work_date' => $date->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'reason' => 'テストテストテスト',
        ], $overridesB));

        // スタッフBの勤怠に基づく休憩データ作成
        BreakTime::factory()->create([
            'work_id' => $this->workB->id,
            'break_start' => $date->copy()->setTime(13, 0),
            'break_end' => $date->copy()->setTime(14, 0),
        ]);
    }
}

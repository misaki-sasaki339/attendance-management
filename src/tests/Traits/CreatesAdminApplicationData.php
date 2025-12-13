<?php

namespace Tests\Traits;

use App\Models\Staff;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Models\Work;
use App\Models\Application;


trait CreatesAdminApplicationData
{
    protected Staff $admin;

    // 管理者の生成
    protected function createAdmin(): void
    {
        $this->admin ??= Staff::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
        ]);
    }

    // スタッフの生成
    protected function createStaff(array $override = []): Staff
    {
        return Staff::factory()->create(array_merge([
            'role' => 'staff',
            'email_verified_at' => now(),
        ], $override));
    }

    // 勤怠と休憩データの生成
    protected function createWorkWithBreaks(Carbon $date, ?Staff $staff = null): Work
    {
        $staff ??= $this->createStaff();

        $work = Work::factory()->create([
            'staff_id' => $staff->id,
            'work_date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
        ]);

        BreakTime::factory()->create([
            'work_id' => $work->id,
            'break_start' => $date->copy()->setTime(12, 0),
            'break_end' => $date->copy()->setTime(13, 0),
        ]);

        return $work->refresh();
    }

    // 承認待ち修正申請の生成
    protected function createPendingApplication(Work $work, array $override = []): Application
    {
        return Application::factory()->create(array_merge([
            'work_id' => $work->id,
            'new_clock_in' => $work->work_date->copy()->setTime(10, 0),
            'new_clock_out' => $work->work_date->copy()->setTime(19, 0),
            'reason' => 'テストテストテスト',
            'status' => 'pending',
        ], $override));
    }

    // 承認済み修正申請の生成
    protected function createApprovedApplication(Work $work, array $override = []): Application
    {
        return Application::factory()->create(array_merge([
            'work_id' => $work->id,
            'new_clock_in' => $work->work_date->copy()->setTime(10, 0),
            'new_clock_out' => $work->work_date->copy()->setTime(10, 0),
            'reason' => 'テストテストテスト',
            'status' => 'approved',
        ], $override));

        $application->approval()->create([
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        return $application->refresh();
    }
}

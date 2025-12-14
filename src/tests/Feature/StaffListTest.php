<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Staff;
use Carbon\Carbon;
use Tests\Traits\CreatesAdminAttendanceData;
use Tests\TestCase;

class StaffListTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminAttendanceData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdmin();
    }

    public function test_admin_can_see_all_staff_names_and_emails()
    {
        $this->actingAs($this->admin, 'admin');

        $staffA = Staff::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $staffB = Staff::factory()->create([
            'name' => 'テスト二郎',
            'email' => 'jiro@example.com',
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->get(route('admin.staffList'));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $staffA->name,
            $staffA->email,
        ]);

        $response->assertSeeInOrder([
            $staffB->name,
            $staffB->email,
        ]);
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_admin_can_see_selected_staff_attendance_list()
    {
        $this->actingAs($this->admin, 'admin');

        $targetMonth = Carbon::create(2025, 12, 1);
        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);

        $response = $this->get(route('admin.staffMonthly', ['id' => $this->staffA->id]));
        $response->assertStatus(200);

        $response->assertSee($this->staffA->name);
        $response->assertSee($targetMonth->format('Y/m'));
        $response->assertSee($date->format('m/d'));
        $response->assertSee($this->workA->clock_in->format('H:i'));
        $response->assertSee($this->workA->clock_out->format('H:i'));
        $response->assertSee($this->workA->break_total);
        $response->assertSee($this->workA->work_total);
    }

    // 「前月」を押した時に表示月の前月の情報が表示される
    public function test_attendance_list_shows_previous_month_records()
    {
        $this->actingAs($this->admin, 'admin');

        $prevMonthDate = Carbon::create(2025, 11, 5);
        $workPrev = $this->createAttendanceForDate($prevMonthDate);

        $response = $this->get(route('admin.staffMonthly', ['id' => $this->staffA->id, 'month' => '2025-11']));
        $response->assertStatus(200);

        $response->assertSee($this->staffA->name);
        $response->assertSee($prevMonthDate->format('Y/m'));
        $response->assertSee($prevMonthDate->format('m/d'));
        $response->assertSee($this->workA->clock_in->format('H:i'));
        $response->assertSee($this->workA->clock_out->format('H:i'));
        $response->assertSee($this->workA->break_total);
        $response->assertSee($this->workA->work_total);
    }

    // 「翌月」を押した時に表示月の翌月の情報が表示される
    public function test_attendance_list_shows_next_month_records()
    {
        $this->actingAs($this->admin, 'admin');

        $nextMonthDate = Carbon::create(2026, 1, 5);
        $workNext = $this->createAttendanceForDate($nextMonthDate);

        $response = $this->get(route('admin.staffMonthly', ['id' => $this->staffA->id, 'month' => '2026-1']));
        $response->assertStatus(200);

        $response->assertSee($this->staffA->name);
        $response->assertSee($nextMonthDate->format('Y/m'));
        $response->assertSee($nextMonthDate->format('m/d'));
        $response->assertSee($this->workA->clock_in->format('H:i'));
        $response->assertSee($this->workA->clock_out->format('H:i'));
        $response->assertSee($this->workA->break_total);
        $response->assertSee($this->workA->work_total);
    }

    //「詳細」を押下するとその日の勤怠詳細画面に遷移する
    public function test_admin_can_see_work_detail_from_attendance_list()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);

        $response = $this->get(route('admin.staffMonthly', ['id' => $this->staffA->id] ));
        $response->assertStatus(200);
        $response->assertSee(route('admin.edit', ['id' => $this->workA->id]));

        $detailResponse = $this->get(route('admin.edit', ['id' => $this->workA->id]));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
    }
}

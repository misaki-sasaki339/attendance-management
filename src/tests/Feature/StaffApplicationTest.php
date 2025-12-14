<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Staff;
use App\Models\Work;
use App\Models\Application;
use Carbon\Carbon;
use Tests\Traits\CreatesStaffApplicationData;

class StaffApplicationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesStaffApplicationData;

    protected $staff;
    protected $admin;
    protected $work;
    protected Carbon $targetDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = Staff::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'role' => 'staff',
        ]);

        $this->admin = Staff::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->targetDate = Carbon::create(2025, 12, 10);
        $this->work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $this->targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_invalid_when_clock_in_is_after_clock_out()
    {
        $this->actingAs($this->staff, 'staff');

        $detail = $this->get(route('attendance.edit', ['id' => $this->work->id] ));
        $detail->assertStatus(200);

        $response = $this->post(route('attendance.application.store', ['id' => $this->work->id]),
        [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'reason' => 'テストテストテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('clock_in');

        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('clock_in'));
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_invalid_when_break_start_is_after_clock_out()
    {
        $this->actingAs($this->staff, 'staff');

        $detail = $this->get(route('attendance.edit', ['id' => $this->work->id] ));
        $detail->assertStatus(200);

        $response = $this->post(route('attendance.application.store', ['id' => $this->work->id]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['19:30'],
            'reason' => 'テストテストテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('break_start.0');

        $this->assertSame('休憩時間が不適切な値です', session('errors')->first('break_start.0'));
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_invalid_when_break_end_is_after_clock_out()
    {
        $this->actingAs($this->staff, 'staff');

        $detail = $this->get(route('attendance.edit', ['id' => $this->work->id] ));
        $detail->assertStatus(200);

        $response = $this->post(route('attendance.application.store', ['id' => $this->work->id]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['19:30'],
            'reason' => 'テストテストテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('break_end.0');

        $this->assertSame('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first('break_end.0'));
    }

    // 備考が未入力の場合、エラーメッセージが表示される
    public function test_invalid_when_reason_is_null()
    {
        $this->actingAs($this->staff, 'staff');

        $detail = $this->get(route('attendance.edit', ['id' => $this->work->id] ));
        $detail->assertStatus(200);

        $response = $this->post(route('attendance.application.store', ['id' => $this->work->id]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'reason' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('reason');

        $this->assertSame('備考を記入してください', session('errors')->first('reason'));
    }

    // 修正申請処理が実行される
    public function test_staff_can_submit_application_successfully()
    {
        $this->actingAs($this->staff, 'staff');

        $this->get(route('attendance.edit', ['id' => $this->work->id] ))
            ->assertStatus(200);

        $application = $this->submitApplication();
        $this->assertNotNull($application);
        $this->assertSame('pending', $application->status);
        $this->assertSame($this->staff->id, $application->work->staff_id);
    }

    // 修正申請が実行され、管理者の承認画面・申請一覧に表示される
    public function test_staff_application_is_visible_for_admin()
    {
        $application = $this->submitApplication();

        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('admin.application.index'));

        $response->assertStatus(200);
        $response->assertSee($this->staff->name);
        $response->assertSee($application->reason);
        $response->assertSee(route('admin.application.show', ['id' => $application->id]));
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_staff_can_see_own_pending_applications()
    {
        $application = $this->submitApplication();

        $this->actingAs($this->staff, 'staff');

        $response = $this->get(route('staff.application.index', ['tab' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        $response->assertSee($application->work->name);
        $response->assertSee($application->reason);
        $response->assertSee(route('staff.application.show', ['id' => $application->id]));
    }

    // 「承認済み」にログインユーザーが行った申請が全て表示されていること
    public function test_staff_can_see_own_approved_applications()
    {
        $application = $this->submitApplication();

        $this->actingAs($this->admin, 'admin');
        $response = $this->post(route('admin.application.approve', ['id' => $application->id]));
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
        $response->assertStatus(302);

        $this->actingAs($this->staff, 'staff');
        $response = $this->get(route('staff.application.index', ['tab' => 'approved']));
        $response->assertStatus(200);

        $response->assertSee($application->work->name);
        $response->assertSee($application->reason);
        $response->assertSee(route('staff.application.show', ['id' => $application->id]));
    }

    // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    public function test_staff_can_see_work_detail_via_application_list()
    {
        $this->actingAs($this->staff, 'staff');

        $application = $this->submitApplication();

        $response = $this->get(route('staff.application.index', ['tab' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee(route('staff.application.show', ['id' => $application->id]));

        $response = $this->get(route('staff.application.show', ['id' => $application->id]));
        $response->assertStatus(200);
        $response->assertSee($application->work->name);
        $response->assertSee($application->new_clock_in->format('H:i'));
        $response->assertSee($application->reason);
    }
}

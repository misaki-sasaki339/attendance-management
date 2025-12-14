<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Traits\CreatesAdminApplicationData;

class AdminApplicationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminApplicationData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdmin();
    }

    // 承認待ちの修正申請が全て表示されている
    public function test_admin_can_see_all_pending_applications()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);

        $workA = $this->createWorkWithBreaks($date);
        $applicationA = $this->createPendingApplication($workA);

        $workB = $this->createWorkWithBreaks($date);
        $applicationB = $this->createPendingApplication($workB);

        $response = $this->get(route('admin.application.index', ['tab' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        $response->assertSee($applicationA->work->name);
        $response->assertSee($applicationA->reason);
        $response->assertSee(route('admin.application.show', ['id' => $applicationA->id]));

        $response->assertSee($applicationB->work->name);
        $response->assertSee($applicationB->reason);
        $response->assertSee(route('admin.application.show', ['id' => $applicationB->id]));
    }

    // 承認済みの修正申請が全て表示されている
    public function test_admin_can_see_all_approved_applications()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);

        $workA = $this->createWorkWithBreaks($date);
        $applicationA = $this->createApprovedApplication($workA);

        $workB = $this->createWorkWithBreaks($date);
        $applicationB = $this->createApprovedApplication($workB);

        $response = $this->get(route('admin.application.index', ['status' => 'approved']));
        $response->assertStatus(200);
        $response->assertSee('承認済み');

        $response->assertSee($applicationA->work->name);
        $response->assertSee($applicationA->reason);
        $response->assertSee(route('admin.application.show', ['id' => $applicationA->id]));

        $response->assertSee($applicationB->work->name);
        $response->assertSee($applicationB->reason);
        $response->assertSee(route('admin.application.show', ['id' => $applicationB->id]));
    }

    // 申請内容が正しく表示されている
    public function test_admin_can_see_application_detail()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);

        $work = $this->createWorkWithBreaks($date);
        $application = $this->createPendingApplication($work);

        $response = $this->get(route('admin.application.show', ['id' => $application->id]));
        $response->assertStatus(200);

        $response->assertSee(('勤怠詳細'));
        $response->assertSee($application->work->name);
        $response->assertSee($application->new_clock_in->format('H:i'));
        $response->assertSee($application->new_clock_out->format('H:i'));
        $response->assertSee($application->reason);
    }

    // 修正申請の承認処理が正しく行われる
    public function test_admin_can_approve_application_and_work_is_updated()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);

        $work = $this->createWorkWithBreaks($date);
        $application = $this->createApprovedApplication($work);

        $response = $this->post(route('admin.application.approve', ['id' => $application->id]));

        $response->assertStatus(302);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'clock_in' => $application->new_clock_in,
            'clock_out' => $application->new_clock_out,
        ]);
    }
}

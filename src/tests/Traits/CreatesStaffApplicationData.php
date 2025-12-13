<?php

namespace Tests\Traits;

use App\Models\Application;
use App\Models\Approval;

trait CreatesStaffApplicationData
{
    protected Application $application;

    // 未承認の修正申請を作成
    protected function submitApplication(array $override = []): Application
    {
        $this->actingAs($this->staff, 'staff');

        $default = [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'reason' => 'テストテストテスト',
        ];

        $response = $this->post(route('attendance.application.store', ['id' => $this->work->id]),
            array_merge($default, $override)
        );
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        return Application::where('work_id', $this->work->id)->first();
    }

    protected function submitAndApproveApplication(array $override = []): Application
    {
        $application = $this->submitApplication($override);

        $application->approval()->create([
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        return $application->fresh();
    }
}

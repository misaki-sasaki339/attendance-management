<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesAdminAttendanceData;
use Carbon\Carbon;
use App\Models\Work;

class AdminWorkTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminAttendanceData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdmin();
    }

    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_admin_can_see_all_attendance_of_the_day()
    {
        $this->actingAs($this->admin, 'admin');
        $date = Carbon::create(2025, 12, 10);
        Carbon::setTestNow($date);

        $this->createAttendanceForDate($date);

        $response = $this->get(route('admin.index'));

        $response->assertSee($date->format('Y/m/d'));

        // スタッフAの勤務データ表示
        $response->assertSeeInOrder([
            $this->staffA->name,
            $this->workA->clock_in->format('H:i'),
            $this->workA->clock_out->format('H:i'),
            $this->workA->break_total,
            $this->workA->work_total,
        ]);

        // スタッフBの勤務データ表示
        $response->assertSeeInOrder([
            $this->staffB->name,
            $this->workB->clock_in->format('H:i'),
            $this->workB->clock_out->format('H:i'),
            $this->workB->break_total,
            $this->workB->work_total,
        ]);

        Carbon::setTestNow();
    }

    // 遷移した際に現在の日付が表示される
    public function test_current_date_is_shown_on_attendance_index()
    {
        $this->actingAs($this->admin, 'admin');
        $date = Carbon::create(2025, 12, 10);
        Carbon::setTestNow($date); // index は「日付に依存する画面」なので setTestNow を使う

        $response = $this->get(route('admin.index'));

        $response->assertSee($date->format('Y/m/d'));

        Carbon::setTestNow();
    }

    // 「前日」ボタンで前日の勤怠が表示される
    public function test_previous_date_attendance_is_shown_when_clicking_previous_button()
    {
        $this->actingAs($this->admin, 'admin');

        // 基準日の設定、勤務データ作成
        $today = Carbon::create(2025, 12, 10);
        Carbon::setTestNow($today);
        $this->createAttendanceForDate($today);

        // 前日のデータ作成
        $yesterday = $today->copy()->subDay();
        $this->createAttendanceForDate($yesterday);

        $responseToday = $this->get(route('admin.index'));
        $responseToday->assertSee($today->format('Y/m/d'));

        $responsePrevious = $this->get('/admin/attendance/list?date=' . $yesterday->toDateString());
        $responsePrevious->assertSee($yesterday->format('Y/m/d'));

        $responsePrevious->assertSeeInOrder([
            $this->staffA->name,
            $this->workA->clock_in->format('H:i'),
            $this->workA->clock_out->format('H:i'),
            $this->workA->break_total,
            $this->workA->work_total,
        ]);

        $responsePrevious->assertSeeInOrder([
            $this->staffB->name,
            $this->workB->clock_in->format('H:i'),
            $this->workB->clock_out->format('H:i'),
            $this->workB->break_total,
            $this->workB->work_total,
        ]);

        $responsePrevious->assertDontSee($today->format('Y/m/d'));

        Carbon::setTestNow();
    }

    // 「翌日」ボタンで翌日の勤怠が表示される
    public function test_next_date_attendance_is_shown_when_clicking_next_button()
    {
        $this->actingAs($this->admin, 'admin');

        // 基準日の設定、勤務データ作成
        $today = Carbon::create(2025, 12, 10);
        Carbon::setTestNow($today);
        $this->createAttendanceForDate($today);

        // 翌日のデータ作成
        $nextDay = $today->copy()->addDay();
        $this->createAttendanceForDate($nextDay);

        $responseToday = $this->get(route('admin.index'));
        $responseToday->assertSee($today->format('Y/m/d'));

        $responseNext = $this->get('/admin/attendance/list?date=' . $nextDay->toDateString());
        $responseNext->assertSee($nextDay->format('Y/m/d'));

        $responseNext->assertSeeInOrder([
            $this->staffA->name,
            $this->workA->clock_in->format('H:i'),
            $this->workA->clock_out->format('H:i'),
            $this->workA->break_total,
            $this->workA->work_total,
        ]);

        $responseNext->assertSeeInOrder([
            $this->staffB->name,
            $this->workB->clock_in->format('H:i'),
            $this->workB->clock_out->format('H:i'),
            $this->workB->break_total,
            $this->workB->work_total,
        ]);

        $responseNext->assertDontSee($today->format('Y/m/d'));

        Carbon::setTestNow();
    }

    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_admin_can_see_selected_work_detail()
    {
        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);
        $break = $this->workA->breakTimes->first();

        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('admin.edit', ['id' => $this->workA->id]));
        $response->assertStatus(200);

        $response->assertSee($this->staffA->name);
        $response->assertSeeInOrder([
            $this->workA->work_date->format('Y年'),
            $this->workA->work_date->format('n月j日'),
        ]);
        $response->assertSee($this->workA->clock_in->format('H:i'));
        $response->assertSee($this->workA->clock_out->format('H:i'));
        $response->assertSee($break->break_start->format('H:i'));
        $response->assertSee($break->break_end->format('H:i'));
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_invalid_when_clock_in_is_after_clock_out()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);

        $detail = $this->get(route('admin.edit', ['id' => $this->workA->id] ));
        $detail->assertStatus(200);

        $response = $this->put(route('admin.update', ['id' => $this->workA->id]),
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
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);
        $break = $this->workA->breakTimes->first();

        $detail = $this->get(route('admin.edit', ['id' => $this->workA->id] ));
        $detail->assertStatus(200);

        $response = $this->put(route('admin.update', ['id' => $this->workA->id]),
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
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);
        $break = $this->workA->breakTimes->first();

        $detail = $this->get(route('admin.edit', ['id' => $this->workA->id] ));
        $detail->assertStatus(200);

        $response = $this->put(route('admin.update', ['id' => $this->workA->id]),
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
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);

        $detail = $this->get(route('admin.edit', ['id' => $this->workA->id] ));
        $detail->assertStatus(200);

        $response = $this->put(route('admin.update', ['id' => $this->workA->id]),
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

    // 修正処理が実行される
    public function test_admin_can_update_work_and_break_time()
    {
        $this->actingAs($this->admin, 'admin');

        $date = Carbon::create(2025, 12, 10);
        $this->createAttendanceForDate($date);

        $this->get(route('admin.edit', ['id' => $this->workA->id] ))
            ->assertStatus(200);

        $response = $this->put(route('admin.update', ['id' => $this->workA->id]),
        [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'reason' => 'テストテストテスト',
        ]);

        $response->assertStatus(302);

        $updateWork = Work::find($this->workA->id);
        $updateBreak = $updateWork->breakTimes->first();

        $this->assertSame('10:00', $updateWork->clock_in->format('H:i'));
        $this->assertSame('19:00', $updateWork->clock_out->format('H:i'));
        $this->assertSame('テストテストテスト', $updateWork->reason);

        $this->assertSame('13:00', $updateBreak->break_start->format('H:i'));
        $this->assertSame('14:00', $updateBreak->break_end->format('H:i'));
    }
}



<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Work;
use App\Models\Staff;
use Carbon\Carbon;

class StaffWorkTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = Staff::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'role' => 'staff',
        ]);
    }

    // 日時取得機能
    public function test_today_view_shows_current_date_time()
    {
        $this->actingAs($this->staff, 'staff');

        $response = $this->get(route('attendance.today'));
        $expectedDate = now()->format('Y年m月d日');
        $expectedWeek = ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek];

        $response->assertSee($expectedDate);
        $response->assertSee($expectedWeek);
        $response->assertSee('id="current-time"', false);
    }

    // ステータス確認機能
    // 勤務外の場合
    public function test_status_shows_not_working()
    {
        $this->actingAs($this->staff, 'staff');

        $response = $this->get(route('attendance.today'));
        $response->assertSee('勤務外');
    }
    // 出勤中
    public function test_status_shows_working()
    {
        $this->actingAs($this->staff, 'staff');

        Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('出勤中');
    }

    // 休憩中
    public function test_status_shows_on_break()
    {
        $this->actingAs($this->staff, 'staff');

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $work->breakTimes()->create([
            'break_start' => '12:00',
            'break_end' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('休憩中');
    }

    // 退勤済
    public function test_status_shows_finished()
    {
        $this->actingAs($this->staff, 'staff');

        Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('退勤済');
    }

    // 出勤機能
    // 出勤ボタンが正しく機能する
    public function test_clock_in_button_updates_status_to_working()
    {
        $this->actingAs($this->staff, 'staff');
        $response = $this->get(route('attendance.today'));
        $response->assertSee('出勤');

        $postResponse = $this->post(route('attendance.clockIn'));
        $postResponse->assertRedirect(route('attendance.today'));

        $work = Work::where('staff_id', $this->staff->id)
            ->whereDate('work_date', today())
            ->first();

        $this->assertNotNull($work);
        $this->assertNotNull($work->clock_in);
        $this->assertNull($work->clock_out);

        $afterResponse = $this->get(route('attendance.today'));
        $afterResponse->assertSee('出勤中');
    }

    // 出勤は一日に一回のみ
    public function test_clock_in_button_is_hidden_after_finished()
    {
        $this->actingAs($this->staff, 'staff');

        Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertDontSee('出勤');
    }

    // 出勤時間が勤怠一覧画面で確認できる
    public function test_clock_in_time_is_shown_in_works_index()
    {
        $this->actingAs($this->staff, 'staff');

        $fixed = Carbon::create(2025,12, 9, 9, 0, 0);
        Carbon::setTestNow($fixed);

        $this->post(route('attendance.clockIn'))
            ->assertRedirect(route('attendance.today'));

        $response = $this->get('/attendance/list?month=2025-12');
        $week = ['日','月','火','水','木','金','土'][$fixed->dayOfWeek];

        $response->assertSeeInOrder([
            $fixed->format('m/d'),
            $week,
            $fixed->format('H:i'),
        ]);

        Carbon::setTestNow();
    }

    // 休憩機能
    // 休憩ボタンが正しく機能する
    public function test_break_start_button_updates_status_to_on_break()
    {
        $this->actingAs($this->staff, 'staff');

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('休憩入');

        $postResponse = $this->post(route('attendance.breakStart'));
        $postResponse->assertRedirect(route('attendance.today'));

        $work = Work::where('staff_id', $this->staff->id)
            ->whereDate('work_date', today())
            ->first();

        $this->assertNotNull($work);
        $this->assertNotNull($work->clock_in);
        $this->assertNull($work->clock_out);

        $break = $work->breakTimes()->latest()->first();
        $this->assertNotNull($break);
        $this->assertNotNull($break->break_start);
        $this->assertNull($break->break_end);

        $afterResponse = $this->get(route('attendance.today'));
        $afterResponse->assertSee('休憩中');
    }

    // 休憩は一日に何回もできる
    public function test_staff_can_start_break_multiple_times()
    {
        $this->actingAs($this->staff, 'staff');

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('休憩入');

        // 1回目の休憩入
        $this->post(route('attendance.breakStart'))
            ->assertRedirect(route('attendance.today'));

        // 1回目の休憩戻
        $this->post(route('attendance.breakEnd'))
            ->assertRedirect(route('attendance.today'));

        // 再度「休憩入」が表示される
        $afterResponse = $this->get(route('attendance.today'));
        $afterResponse->assertSee('休憩入');
    }

    public function test_break_end_button_updates_status_to_working()
    {
        $this->actingAs($this->staff, 'staff');

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('休憩入');

        // 1回目の休憩入
        $this->post(route('attendance.breakStart'))
            ->assertRedirect(route('attendance.today'));

        // 1回目の休憩戻
        $this->post(route('attendance.breakEnd'))
            ->assertRedirect(route('attendance.today'));

        // ステータスが「出勤中」になる
        $afterResponse = $this->get(route('attendance.today'));
        $afterResponse->assertSee('出勤中');
    }

    // 休憩は一日に何回も取得できる
    public function test_staff_can_end_break_multiple_times()
    {
        $this->actingAs($this->staff, 'staff');

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('休憩入');

        // 1回目の休憩入
        $this->post(route('attendance.breakStart'))
            ->assertRedirect(route('attendance.today'));

        // 1回目の休憩戻
        $this->post(route('attendance.breakEnd'))
            ->assertRedirect(route('attendance.today'));

        // 2回目の休憩入
        $this->post(route('attendance.breakStart'))
            ->assertRedirect(route('attendance.today'));

        // 休憩戻ボタンが表示される
        $afterResponse = $this->get(route('attendance.today'));
        $afterResponse->assertSee('休憩戻');
    }


    // 休憩時間が勤怠一覧画面で確認できる
    public function test_break_time_is_shown_in_works_index()
    {
        $this->actingAs($this->staff, 'staff');

        $targetDate = Carbon::create(2025, 12, 9);

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        // 固定時刻で休憩開始
        Carbon::setTestNow(Carbon::create(2025, 12, 9, 12, 0));
        $this->post(route('attendance.breakStart'))
            ->assertRedirect(route('attendance.today'));

        // 固定時刻で休憩終了
        Carbon::setTestNow(Carbon::create(2025, 12, 9, 13, 0));
        $this->post(route('attendance.breakEnd'))
            ->assertRedirect(route('attendance.today'));

        $response = $this->get('/attendance/list?month=2025-12');
        $week = ['日','月','火','水','木','金','土'][$targetDate->dayOfWeek];

        $expectedBreakTotal = $work->fresh()->break_total;

        $response->assertSeeInOrder([
            $targetDate->format('m/d'),
            $week,
            $work->clock_in->format('H:i'),
            $expectedBreakTotal,
        ]);

        Carbon::setTestNow();
    }

    // 退勤機能
    // 退勤ボタンが正しく機能する
    public function test_clock_out_button_updates_status_to_finished()
    {
        $this->actingAs($this->staff, 'staff');

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.today'));
        $response->assertSee('退勤');

        $postResponse = $this->post(route('attendance.clockOut'));
        $postResponse->assertRedirect(route('attendance.today'));

        $work = $work->fresh();
        $this->assertNotNull($work);
        $this->assertNotNull($work->clock_in);
        $this->assertNotNull($work->clock_out);

        $after = $this->get(route('attendance.today'));
        $after->assertSee('退勤済');
    }

    // 退勤時間が勤怠一覧画面で確認できる
    public function test_clock_out_time_is_shown_in_works_index()
    {
        $this->actingAs($this->staff, 'staff');

        $response = $this->get(route('attendance.today'));
        $response->assertSee('勤務外');

        $fixed = Carbon::create(2025,12, 9, 9, 0, 0);
        Carbon::setTestNow($fixed);

        $this->post(route('attendance.clockIn'))
            ->assertRedirect(route('attendance.today'));

        $clockOutFixed = $fixed->copy()->setTime(18, 0);
        Carbon::setTestNow($clockOutFixed);

        $this->post(route('attendance.clockOut'))
            ->assertRedirect(route('attendance.today'));

        $response = $this->get('/attendance/list?month=2025-12');
        $week = ['日','月','火','水','木','金','土'][$fixed->dayOfWeek];

        $response->assertSeeInOrder([
            $fixed->format('m/d'),
            $week,
            $fixed->format('H:i'),
            $clockOutFixed->format('H:i'),
        ]);

        Carbon::setTestNow();
    }

    // 勤怠一覧情報取得機能
    // 自分が行った勤怠情報が全て表示されている
    public function test_staff_can_see_only_their_attendance_records()
    {
        $this->actingAs($this->staff, 'staff');

        $w1Date = Carbon::create(2025, 12, 1);
        $w2Date = Carbon::create(2025, 12, 15);


        $work1 = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $w1Date->toDateString(),
            'clock_in' => '08:00',
            'clock_out' => '17:00',
        ]);

        $work2 = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $w2Date->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        Work::factory()->create([
            'staff_id' => Staff::factory()->create()->id,
            'work_date' => '2025-12-05',
            'clock_in' => '03:00',
            'clock_out' => '15:00',
        ]);

        $w1_date = $work1->work_date->format('m/d');
        $w1_in   = $work1->clock_in->format('H:i');
        $w1_out  = $work1->clock_out->format('H:i');

        $w2_date = $work2->work_date->format('m/d');
        $w2_in   = $work2->clock_in->format('H:i');
        $w2_out  = $work2->clock_out->format('H:i');

        $response = $this->get('/attendance/list?month=2025-12');

        // 自分の勤怠データが全て表示されている
        $response->assertSeeInOrder([$w1_date, $w1_in, $w1_out]);
        $response->assertSeeInOrder([$w2_date, $w2_in, $w2_out]);

        // 他人の勤怠データは表示されない(カレンダー表示のため時間のみ判定)
        $response->assertDontSee('03:00');
        $response->assertDontSee('15:00');
    }

    // 勤怠一覧画面遷移時に現在の月が表示される
    public function test_attendance_list_shows_current_month()
    {
        $this->actingAs($this->staff, 'staff');

        $fixed = Carbon::create(2025, 12, 9);
        Carbon::setTestNow($fixed);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('2025/12');

        Carbon::setTestNow();
    }

    // 「前月」を押した時に表示月の前月の情報が表示される
    public function test_attendance_list_shows_only_previous_month_records()
    {
        $this->actingAs($this->staff, 'staff');

        $prevDate     = Carbon::create(2025, 11, 5);
        $prevIn       = Carbon::create(2025, 11, 5, 8, 0);
        $prevOut      = Carbon::create(2025, 11, 5, 17, 0);

        $currentDate  = Carbon::create(2025, 12, 15);
        $currentIn    = Carbon::create(2025, 12, 15, 9, 0);
        $currentOut   = Carbon::create(2025, 12, 15, 18, 0);

        // 前月の勤務データ
        $workPrev = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $prevDate->toDateString(),
            'clock_in' => $prevIn,
            'clock_out' => $prevOut,
        ]);

        // 当月の勤務データ
        $workCurrent = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $currentDate->toDateString(),
            'clock_in' => $currentIn,
            'clock_out' => $currentOut,
        ]);

        $response = $this->get('/attendance/list?month=2025-12');
        $response->assertSeeInOrder([
            $currentDate->format('m/d'),
            $currentIn->format('H:i'),
            $currentOut->format('H:i'),
        ]);

        $response->assertDontSee($prevDate->format('m/d'));
        $response->assertDontSee($prevIn->format('H:i'));
        $response->assertDontSee($prevOut->format('H:i'));

        $responsePrev = $this->get('/attendance/list?month=2025-11');
        $responsePrev->assertSeeInOrder([
            $prevDate->format('m/d'),
            $prevIn->format('H:i'),
            $prevOut->format('H:i'),
        ]);

        $responsePrev->assertDontSee($currentDate->format('m/d'));
        $responsePrev->assertDontSee($currentIn->format('H:i'));
        $responsePrev->assertDontSee($currentOut->format('H:i'));
    }

    // 「翌月」を押した時に表示月の翌月の情報が表示される
    public function test_attendance_list_shows_only_next_month_records()
    {
        $this->actingAs($this->staff, 'staff');

        $currentDate     = Carbon::create(2025, 12, 5);
        $currentIn       = Carbon::create(2025, 12, 5, 8, 0);
        $currentOut      = Carbon::create(2025, 12, 5, 17, 0);

        $nextDate  = Carbon::create(2026, 1, 15);
        $nextIn    = Carbon::create(2026, 1, 15, 9, 0);
        $nextOut   = Carbon::create(2026, 1, 15, 18, 0);

        // 当月の勤務データ
        $workCurrent = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $currentDate->toDateString(),
            'clock_in' => $currentIn,
            'clock_out' => $currentOut,
        ]);

        // 翌月の勤務データ
        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $nextDate->toDateString(),
            'clock_in' => $nextIn,
            'clock_out' => $nextOut,
        ]);

        $response = $this->get('/attendance/list?month=2025-12');
        $response->assertSeeInOrder([
            $currentDate->format('m/d'),
            $currentIn->format('H:i'),
            $currentOut->format('H:i'),
        ]);

        $response->assertDontSee($nextDate->format('m/d'));
        $response->assertDontSee($nextIn->format('H:i'));
        $response->assertDontSee($nextOut->format('H:i'));

        $responseNext = $this->get('/attendance/list?month=2026-1');
        $responseNext->assertSeeInOrder([
            $nextDate->format('m/d'),
            $nextIn->format('H:i'),
            $nextOut->format('H:i'),
        ]);
        $responseNext->assertDontSee($currentDate->format('m/d'));
        $responseNext->assertDontSee($currentIn->format('H:i'));
        $responseNext->assertDontSee($currentOut->format('H:i'));
    }

    // 詳細ボタンを押すと勤怠詳細ページに遷移
    public function test_detail_button_redirects_to_work_detail_page()
    {
        $this->actingAs($this->staff, 'staff');

        $targetDate = Carbon::create(2025, 12, 1);
        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get('/attendance/list?month=2025-12');
        $detailUrl = route('attendance.edit', ['id' => $work->id]);
        $response->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
    }

    // 勤怠詳細画面の名前がログインユーザーの名前になっている
    public function test_work_detail_page_shows_logged_in_staff_name()
    {
        $this->actingAs($this->staff, 'staff');

        $targetDate = Carbon::create(2025, 12, 1);
        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $detailUrl = route('attendance.edit', ['id' => $work->id]);

        $this->get('/attendance/list?month=2025-12')
            ->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
        $detailResponse->assertSee($this->staff->name);
    }

    // 勤怠詳細画面の日付が選択した日付になっている
    public function test_work_detail_page_shows_logged_in_work_date()
    {
        $this->actingAs($this->staff, 'staff');

        $targetDate = Carbon::create(2025, 12, 1);

        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $detailUrl = route('attendance.edit', ['id' => $work->id]);

        $this->get('/attendance/list?month=2025-12')
            ->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');

        $detailResponse->assertSeeInOrder([
            $targetDate->format('Y年'),
            $targetDate->format('n月j日'),
        ]);
    }

    // 勤怠詳細画面の出勤・退勤時刻がスタッフの打刻時間になっている
    public function test_work_detail_page_shows_logged_in_work_time()
    {
        $this->actingAs($this->staff, 'staff');

        $targetDate = Carbon::create(2025, 12, 1);
        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $detailUrl = route('attendance.edit', ['id' => $work->id]);

        $this->get('/attendance/list?month=2025-12')
            ->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');

        $detailResponse->assertSeeInOrder([
            $work->clock_in->format('H:i'),
            $work->clock_out->format('H:i'),
        ]);
    }

    // 勤怠詳細画面の休憩時刻がスタッフの打刻時間になっている
    public function test_work_detail_page_shows_logged_in_break_time()
    {
        $this->actingAs($this->staff, 'staff');

        $targetDate = Carbon::create(2025, 12, 1);
        $work = Work::factory()->create([
            'staff_id' => $this->staff->id,
            'work_date' => $targetDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $break = $work->breakTimes()->create([
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        $detailUrl = route('attendance.edit', ['id' => $work->id]);

        $this->get('/attendance/list?month=2025-12')
            ->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');

        $detailResponse->assertSeeInOrder([
            $break->break_start->format('H:i'),
            $break->break_end->format('H:i'),
        ]);
    }
}

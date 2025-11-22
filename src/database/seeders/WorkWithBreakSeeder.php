<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Work;
use App\Models\Staff;
use App\Models\BreakTime;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class WorkWithBreakSeeder extends Seeder
{
    public function run(): void
    {
        $period = CarbonPeriod::create('2025-08-01', '2025-10-31');
        $staffs = Staff::where('role', 'staff')->get();

        foreach ($staffs as $staff) {
            foreach ($period as $date) {
                /** @var \Carbon\Carbon $date */

                // 土日はお休み
                if ($date->isWeekend()) {
                    continue;
                }
                // Workの生成
                $work = Work::factory()->create([
                    'staff_id' => $staff->id,
                    'work_date' => $date,
                    'clock_in' => Carbon::parse($date)->setTime(9, 0),
                    'clock_out' => Carbon::parse($date)->setTime(18, 0),
                ]);

                BreakTime::create([
                    'work_id' => $work->id,
                    'break_start' => Carbon::parse($date)->setTime(12, 0),
                    'break_end' => Carbon::parse($date)->setTime(13, 0),
                ]);
            }
        }
    }
}

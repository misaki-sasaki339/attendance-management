<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Work;
use App\Models\BreakTime;
use Carbon\CarbonPeriod;

class WorkWithBreakSeeder extends Seeder
{
    public function run(): void
    {
        $period = CarbonPeriod::create('2025-11-01', '2025-11-30');

        foreach ($period as $date) {

            // Workの生成
            $work = Work::factory()->create([
                'staff_id' => 1,
                'work_date' => $date,
            ]);

            // 1~2回の休憩をランダムで生成
            $breakCount = rand(0, 2);
            for ($i = 0; $i < $breakCount; $i++) {
                BreakTime::factory()->create([
                    'work_id' => $work->id,
                ]);
            }
        }
    }
}

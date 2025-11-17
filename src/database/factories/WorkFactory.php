<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Work;
use Carbon\Carbon;

class WorkFactory extends Factory
{
    public function definition(): array
    {
        // 出勤打刻 8:00~10:59までのランダム
        $clockIn = Carbon::today()->setTime($this->faker->numberBetween(8,10), $this->faker->numberBetween(0,59));
        $clockOut = (clone $clockIn)->addHours($this->faker->numberBetween(7,10));
        return [
            'staff_id' => 2,
            'work_date' => Carbon::today(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}

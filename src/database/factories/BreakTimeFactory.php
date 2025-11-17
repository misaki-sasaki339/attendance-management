<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    public function definition(): array
    {
        // 11:00~15:59の間で休憩打刻
        $breakStart = Carbon::today()
            ->setTime($this->faker->numberBetween(11, 15), $this->faker->numberBetween(0, 59));

        // 30分〜90分の間で休憩
        $breakEnd = (clone $breakStart)->addMinutes($this->faker->numberBetween(30, 90));
        return [
            'work_id' => 1,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}

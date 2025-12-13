<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Application;
use App\Models\Work;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(),
            'new_clock_in' => now()->setTime(10, 0),
            'new_clock_out' => now()->setTime(19, 0),
            'reason' => 'テストテストテスト',
            'status' => 'pending',

        ];
    }

    public function approved(): static
    {
        return $this->state(fn() => ['status' => 'approved']);
    }
}

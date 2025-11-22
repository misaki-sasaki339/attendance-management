<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Staff;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => fake('ja_JP')->lastName . ' ' . fake('ja_JP')->firstName,
            'email' => $this->faker->safeEmail(),
            'password' => bcrypt('password123'),
        ];
    }
}

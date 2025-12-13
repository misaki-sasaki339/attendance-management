<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Staff;
use Illuminate\Support\Carbon;

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

    public function unverified()
    {
        return $this->state(function () {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

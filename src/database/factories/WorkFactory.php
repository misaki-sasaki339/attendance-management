<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clock_in' => null,
            'clock_out' => null,
        ];
    }
}

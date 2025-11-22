<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Work;
use Carbon\Carbon;

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

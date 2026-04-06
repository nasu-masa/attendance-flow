<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BreakLogFactory extends Factory
{
    public function definition()
    {
        return [
            'attendance_id' => null,
            'break_start'   => null,
            'break_end'     => null,
        ];
    }
}

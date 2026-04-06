<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id'   => User::factory(),
            'date'      => now(),
            'status'    => Attendance::STATUS_OUT,
            'clock_in'  => null,
            'clock_out' => null,
            'remarks'   => null,
        ];
    }
}



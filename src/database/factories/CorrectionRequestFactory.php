<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRequestFactory extends Factory
{
    protected $pending = CorrectionRequest::STATUS_PENDING;
    protected $approved = CorrectionRequest::STATUS_APPROVED;

    public function definition()
    {
        return [
            'user_id'       => User::factory(),
            'attendance_id' => Attendance::factory(),
            'request_type'  => 'clock_in',
            'before_value'  => [
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'remarks'   => 'before',
                ],
            'after_value'   => [
                'clock_in'  => '10:00',
                'clock_out' => '18:00',
                'remarks'   => 'after',
                ],
            'status'        => $this->pending,
            'approved_by'   => null,
            'approved_at'   => null,
            'remarks'       => $this->faker->sentence()
        ];
    }

    public function pending()
    {
        return $this->state(fn() => [
            'status' => $this->pending,
        ]);
    }

    public function approved()
    {
        return $this->state(fn() => [
            'status'      => $this->approved,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }
}


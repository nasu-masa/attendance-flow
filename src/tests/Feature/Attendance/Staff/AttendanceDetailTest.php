<?php

namespace Tests\Feature\Attendance\Staff;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に正しい情報が表示される()
    {
        $user = User::factory()->create([
            'name' => '茄子田常夫',
        ]);

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => today()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendance->breakLogs()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end'   => '13:00',
        ]);

        $response = $this->get(route('staff.attendance.detail', $attendance->id));

        $response->assertSee('茄子田常夫');

        $response->assertSee($attendance->date->isoFormat('YYYY年'));
        $response->assertSee($attendance->date->isoFormat('M月D日'));

        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));

        $response->assertSee($attendance->break1->break_start->format('H:i'));
        $response->assertSee($attendance->break1->break_end->format('H:i'));
    }
}

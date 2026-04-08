<?php

namespace Tests\Feature\Attendance\Admin;

use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BaseAdminAttendanceTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    protected function makeStaff(): User
    {
        return User::factory()->staff()->create();
    }

    protected function makeAttendance(User $user, $date, array $attributes = [])
    {
        $attendance = Attendance::factory()->create(array_merge([
            'user_id'   => $user->id,
            'date'      => $date,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ], $attributes));

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => '12:00',
            'break_end'     => '13:00',
        ]);

        return $attendance;
    }

    protected function assertAttendanceRow($response)
    {
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    protected function assertCorrectionVisible($response, CorrectionRequest $req, string $statusLabel, )
    {
        $response->assertSee($statusLabel);
        $response->assertSee($req->remarks);

        $response->assertSee($req->user->name);

        $response->assertSee($req->attendance->date->isoFormat('YYYY/MM/DD'));
    }
}

<?php

namespace Tests\Feature\Attendance;

use Tests\Feature\Attendance\Staff\BaseStaffAttendanceTestCase;
use App\Models\Attendance;

class StatusTest extends BaseStaffAttendanceTestCase
{
    protected function assertStatusDisplay($status, $statusText)
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date'    => $this->today->toDateString(),
            'status'  => $status,
        ]);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertSee($statusText);
    }

    public function test_勤務外の場合_勤怠ステータスが正しく表示される()
    {
        $this->assertStatusDisplay(Attendance::STATUS_OUT, '勤務外');
    }

    public function test_出勤中の場合_勤怠ステータスが正しく表示される()
    {
        $this->assertStatusDisplay(Attendance::STATUS_WORKING, '出勤中');
    }

    public function test_休憩中の場合_勤怠ステータスが正しく表示される()
    {
        $this->assertStatusDisplay(Attendance::STATUS_BREAK, '休憩中');
    }

    public function test_退勤済の場合_勤怠ステータスが正しく表示される()
    {
        $this->assertStatusDisplay(Attendance::STATUS_FINISHED, '退勤済');
    }
}

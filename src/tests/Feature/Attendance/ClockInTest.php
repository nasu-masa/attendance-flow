<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use Tests\Feature\Attendance\Staff\BaseStaffAttendanceTestCase;

class ClockInTest extends BaseStaffAttendanceTestCase
{
    protected function createAttendance($status)
    {
        return Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date'    => $this->today->toDateString(),
            'status'  => $status,
        ]);
    }

    public function test_出勤ボタンが正しく機能する()
    {
        $this->createAttendance(Attendance::STATUS_OUT);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertSee('p-attendance__button--start');
    }

    public function test_出勤処理後に画面上に表示されるステータスが「勤務中」になる()
    {
        $this->post(route('staff.attendance.action'), [
            'action' => Attendance::ACTION_START,
        ]);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('date', today()->toDateString())
            ->first();

        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);
    }

    public function test_出勤は一日一回のみできる()
    {
        $this->createAttendance(Attendance::STATUS_FINISHED);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertDontSee('p-attendance__button--start');
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $attendance = $this->createAttendance(Attendance::STATUS_OUT);

        $this->post(route('staff.attendance.action'), [
            'action' => Attendance::ACTION_START,
        ]);

        $response = $this->get(route('staff.attendance.list'));

        $response->assertSee($attendance->fresh()->clock_in->format('H:i'));
    }
}

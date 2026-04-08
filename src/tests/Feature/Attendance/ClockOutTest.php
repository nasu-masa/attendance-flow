<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use Tests\Feature\Attendance\Staff\BaseStaffAttendanceTestCase;

class ClockOutTest extends BaseStaffAttendanceTestCase
{
    protected function createAttendance($status)
    {
        return Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date'    => $this->today->toDateString(),
            'status'  => $status,
        ]);
    }

    public function test_退勤ボタンが正しく機能する()
    {
        $this->createAttendance(Attendance::STATUS_FINISHED);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertDontSee('p-attendance__button--start');
    }

    public function test_画面上に「退勤」ボタンが表示される()
    {
        $this->createAttendance(Attendance::STATUS_WORKING);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertSee('p-attendance__button--finish');
    }

    public function test_退勤処理後に画面上に表示されるステータスが「退勤済」になる()
    {
        $attendance = $this->createAttendance(Attendance::STATUS_WORKING);

        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_FINISH]);

        $attendance->refresh();

        $this->assertEquals(Attendance::STATUS_FINISHED, $attendance->status);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $attendance = $this->createAttendance(Attendance::STATUS_OUT);

        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_START]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_FINISH]);

        $attendance->refresh();

        $attendance->update([
            'clock_out' => $attendance->clock_in->copy()->addHours(9)->addMinutes(43),
        ]);

        $response = $this->get(route('staff.attendance.list'));

        $displayDate = $attendance->date->format('m/d') . '(' . $attendance->date->isoFormat('dd') . ')';

        $response->assertSeeInOrder([
            $displayDate,
            $attendance->clock_out->format('H:i'),
        ]);
    }
}

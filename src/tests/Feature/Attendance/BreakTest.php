<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakLog;
use Tests\Feature\Attendance\Staff\BaseStaffAttendanceTestCase;

class BreakTest extends BaseStaffAttendanceTestCase
{
    protected function createWorkingAttendance()
    {
        $attendance = $this->makeEmptyAttendance($this->today);
        $attendance->update(['status' => Attendance::STATUS_WORKING]);

        return [$this->user, $attendance];
    }

    public function test_出勤中の場合_休憩入ボタンが表示される()
    {
        [$user, $attendance] = $this->createWorkingAttendance();

        $response = $this->get(route('staff.attendance.index'));

        $response->assertSee('p-attendance__button--break-in');
    }

    public function test_休憩入処理後_ステータスが休憩中になる()
    {
        [$user, $attendance] = $this->createWorkingAttendance();

        $this->post(route('staff.attendance.action'), [
            'action' => Attendance::ACTION_BREAK_IN,
        ]);

        $attendance->refresh();

        $this->assertEquals(Attendance::STATUS_BREAK, $attendance->status);
    }

    public function test_休憩は一日に何回でもできる()
    {
        [$user, $attendance] = $this->createWorkingAttendance();

        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_OUT]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_OUT]);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertSee('p-attendance__button--break-in');
    }

    public function test_休憩戻処理後_ステータスが出勤中になる()
    {
        [$user, $attendance] = $this->createWorkingAttendance();

        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_OUT]);

        $attendance->refresh();

        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        [$user, $attendance] = $this->createWorkingAttendance();

        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_OUT]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_OUT]);
        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);

        $response = $this->get(route('staff.attendance.index'));

        $response->assertSee('p-attendance__button--break-out');
    }

    public function test_休憩時間が勤怠一覧画面で確認できる()
    {
        [$user, $attendance] = $this->createWorkingAttendance();

        $this->post(route('staff.attendance.action'), ['action' => Attendance::ACTION_BREAK_IN]);

        $breakLog = BreakLog::where('attendance_id', $attendance->id)->first();
        $breakLog->update([
            'break_end' => $breakLog->break_start->copy()->addHours(3)->addMinutes(45),
        ]);

        $attendance->refresh();

        $response = $this->get(route('staff.attendance.list'));

        $displayDate = $attendance->date->format('m/d') . '(' . $attendance->date->isoFormat('dd') . ')';

        $response->assertSeeInOrder([
            $displayDate,
            '3:45',
        ]);
    }
}

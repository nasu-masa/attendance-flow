<?php

namespace Tests\Feature\Attendance\Staff;

use App\Models\Attendance;

class AttendanceListTest extends BaseStaffAttendanceTestCase
{
    protected function makeAttendance($date)
    {
        return $this->makeFullAttendance($date);
    }

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $attendances = collect([
            $this->makeAttendance($this->baseDate),
            $this->makeAttendance($this->baseDate->copy()->addDay()),
            $this->makeAttendance($this->baseDate->copy()->addDays(2)),
        ]);

        $response = $this->get(route('staff.attendance.list'));

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date->isoFormat('MM/DD(dd)'));
            $this->assertAttendanceRow($response);
        }
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $now = now();

        $response = $this->get(route('staff.attendance.list'));

        $response->assertSee($now->format('Y/m'));
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $prev = $this->baseDate->copy()->subMonth();

        $attendance = $this->makeAttendance($prev->copy()->startOfMonth());

        $response = $this->get(route('staff.attendance.list', [
            'year'  => $prev->year,
            'month' => $prev->month,
        ]));

        $response->assertSee($prev->format('Y/m'));
        $response->assertSee($attendance->date->isoFormat('MM/DD(dd)'));
        $this->assertAttendanceRow($response);
    }

    public function test_翌月を押下した時に表示月の前月の情報が表示される()
    {
        $next = $this->baseDate->copy()->addMonth();

        $attendance = $this->makeAttendance($next->copy()->startOfMonth());

        $response = $this->get(route('staff.attendance.list', [
            'year'  => $next->year,
            'month' => $next->month,
        ]));

        $response->assertSee($next->format('Y/m'));
        $response->assertSee($attendance->date->isoFormat('MM/DD(dd)'));
        $this->assertAttendanceRow($response);
    }

    public function test_詳細を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date'    => $this->today->toDateString(),
        ]);

        $response = $this->get(route('staff.attendance.list'));

        $response->assertSee(route('staff.attendance.detail', $attendance->id));
    }
}

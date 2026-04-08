<?php

namespace Tests\Feature\Attendance\Admin;

use App\Models\Attendance;
use Tests\Feature\Attendance\Admin\BaseAdminAttendanceTestCase;

class AdminStaffAttendanceListTest extends BaseAdminAttendanceTestCase
{
    protected $staff;
    protected string $attendanceListUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = $this->makeStaff();

        $this->attendanceListUrl = route('admin.attendance.staff', $this->staff->id);
    }

    public function test_管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $user = $this->makeStaff();

        $response = $this->get(route('admin.staff.list'));

        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $attendance = $this->makeAttendance($this->staff, now());

        $response = $this->get($this->attendanceListUrl);

        $response->assertSee($attendance->date->isoFormat('MM/DD(dd)'));
        $this->assertAttendanceRow($response);
    }

    public function test_前月を押下したときに表示月の前月の情報が表示される()
    {
        $prev = now()->subMonth();

        $attendance = $this->makeAttendance(
            $this->staff,
            $prev->copy()->startOfMonth()
        );

        $response = $this->get($this->attendanceListUrl . "?year={$prev->year}&month={$prev->month}");

        $response->assertSee($prev->format('Y/m'));
        $response->assertSee($attendance->date->isoFormat('MM/DD(dd)'));
        $this->assertAttendanceRow($response);
    }

    public function test_翌月を押下したときに表示月の翌月の情報が表示される()
    {
        $next = now()->addMonth();

        $attendance = $this->makeAttendance(
            $this->staff,
            $next->copy()->startOfMonth()
        );

        $response = $this->get($this->attendanceListUrl . "?year={$next->year}&month={$next->month}");

        $response->assertSee($next->format('Y/m'));
        $response->assertSee($attendance->date->isoFormat('MM/DD(dd)'));
        $this->assertAttendanceRow($response);
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->staff->id,
        ]);

        $response = $this->get($this->attendanceListUrl);

        $response->assertSee(route('admin.attendance.detail', $attendance->id));
    }
}

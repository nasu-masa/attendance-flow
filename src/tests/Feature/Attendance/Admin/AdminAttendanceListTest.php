<?php

namespace Tests\Feature\Attendance\Admin;

use Tests\Feature\Attendance\Admin\BaseAdminAttendanceTestCase;

class AdminAttendanceListTest extends BaseAdminAttendanceTestCase
{
    protected $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->today = today();
    }

    protected function assertAttendanceTableHeaders($response)
    {
        $response->assertSee('名前');
        $response->assertSee('出勤');
        $response->assertSee('退勤');
        $response->assertSee('休憩');
        $response->assertSee('合計');
        $response->assertSee('詳細');
    }

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $user = $this->makeStaff();

        $this->makeAttendance($user, $this->today);

        $response = $this->get(route('admin.attendance.list'));

        $this->assertAttendanceTableHeaders($response);

        $response->assertSee($user->name);
        $this->assertAttendanceRow($response);
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        $response = $this->get(route('admin.attendance.list'));

        $response->assertSee($this->today->format('Y/m/d'));
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        $yesterday = $this->today->copy()->subDay();

        $user = $this->makeStaff();
        $this->makeAttendance($user, $yesterday);

        $response = $this->get(route('admin.attendance.list', [
            'date' => $yesterday->toDateString(),
        ]));

        $this->assertAttendanceTableHeaders($response);

        $response->assertSee($yesterday->format('Y/m/d'));
        $response->assertSee($user->name);
        $this->assertAttendanceRow($response);
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        $tomorrow = $this->today->copy()->addDay();

        $user = $this->makeStaff();
        $this->makeAttendance($user, $tomorrow);

        $response = $this->get(route('admin.attendance.list', [
            'date' => $tomorrow->toDateString(),
        ]));

        $this->assertAttendanceTableHeaders($response);

        $response->assertSee($tomorrow->format('Y/m/d'));
        $response->assertSee($user->name);
        $this->assertAttendanceRow($response);
    }
}

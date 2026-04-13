<?php

namespace Tests\Feature\Attendance\Admin;


class AdminAttendanceDetailTest extends BaseAdminAttendanceTestCase
{
    protected $staff;
    protected $attendance;
    protected string $detailUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = $this->makeStaff();

        $this->attendance = $this->makeAttendance($this->staff, today(), [
            'remarks' => 'テスト備考',
        ]);

        $this->detailUrl = route('admin.attendance.detail', $this->attendance->id);
    }

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $response = $this->get($this->detailUrl);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト備考');
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $response = $this->patch($this->detailUrl, [
            'clock_in'    => '19:00',
            'clock_out'   => '18:00',
            'breaks'    => [
                ['start' => '19:00', 'end' => '20:00']
            ],
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $response = $this->patch($this->detailUrl, [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'breaks'    => [
                ['start' => '19:00', 'end' => '20:00']
            ],
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $response = $this->patch($this->detailUrl, [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'breaks'    => [
                ['start' => '19:00', 'end' => '20:00']
            ],
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $response = $this->patch($this->detailUrl, [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'breaks'    => [
                ['start' => '19:00', 'end' => '20:00']
            ],
            'remarks'     => ''
        ]);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
}

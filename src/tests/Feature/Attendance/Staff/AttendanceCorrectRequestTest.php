<?php

namespace Tests\Feature\Attendance\Staff;

use App\Models\CorrectionRequest;
use App\Models\User;

class AttendanceCorrectRequestTest extends BaseStaffAttendanceTestCase
{
    protected function actingUserWithAttendance()
    {
        $user = $this->user;

        $attendance = $this->makeEmptyAttendance($this->today);

        return [$user, $attendance];
    }

    protected function createPendingRequest()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();

        $this->post(route('staff.attendance.detail.post', ['id' => $attendance->id]), [
            'clock_in'      => '10:00',
            'clock_out'     => '18:00',
            'break_start_1' => '12:00',
            'break_end_1'   => '13:00',
            'remarks'       => '通院のため',
        ]);

        $this->assertDatabaseHas('correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => CorrectionRequest::STATUS_PENDING,
        ]);

        return [$user, $attendance, CorrectionRequest::first()];
    }

    public function test_出勤時間が退勤時間より後ならエラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();

        $this->from(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->post(route('staff.attendance.detail.post', ['id' => $attendance->id]), [
                'clock_in'  => '19:00',
                'clock_out' => '18:00',
                'remarks'   => 'test',
            ])
            ->assertSessionHasErrors(['clock_out']);

        $this->get(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_休憩開始時間が退勤時間より後ならエラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();

        $this->from(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->post(route('staff.attendance.detail.post', ['id' => $attendance->id]), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_start_1' => '20:00',
                'remarks'       => 'test',
            ])
            ->assertSessionHasErrors(['break_start_1']);

        $this->get(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->assertSee('休憩時間が不適切な値です');
    }

    public function test_休憩終了時間が退勤時間より後ならエラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();

        $this->from(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->post(route('staff.attendance.detail.post', ['id' => $attendance->id]), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_start_1' => '12:00',
                'break_end_1'   => '20:00',
                'remarks'       => 'test',
            ])
            ->assertSessionHasErrors(['break_end_1']);

        $this->get(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_備考欄が未入力ならエラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();

        $this->from(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->post(route('staff.attendance.detail.post', ['id' => $attendance->id]), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_start_1' => '12:00',
                'break_end_1'   => '13:00',
                'remarks'       => '',
            ])
            ->assertSessionHasErrors(['remarks']);

        $this->get(route('staff.attendance.detail', ['id' => $attendance->id]))
            ->assertSee('備考を記入してください');
    }

    protected function assertCorrectionVisible($response, CorrectionRequest $correction, $statusLabel)
    {
        $response->assertSee($statusLabel);
        $response->assertSee($correction->remarks);
        $response->assertSee($correction->user->name);
    }

    public function test_修正申請が作成され管理者側に表示される()
    {
        [$user, $attendance, $correction] = $this->createPendingRequest();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.attendance.correction.list'));

        $this->assertCorrectionVisible($response, $correction, '承認待ち');
    }

    public function test_承認待ち一覧に自分の申請が表示される()
    {
        [$user, $attendance, $correction] = $this->createPendingRequest();

        $response = $this->actingAs($user)
            ->get(route('staff.attendance.correction.list', ['tab' => CorrectionRequest::STATUS_PENDING]));

        $this->assertCorrectionVisible($response, $correction, '承認待ち');
    }

    public function test_承認済み一覧に承認された申請が表示される()
    {
        [$user, $attendance, $correction] = $this->createPendingRequest();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'admin')
            ->patch(route('admin.attendance.correction.approve', ['attendance_correct_request_id' => $correction->id]));

        $correction->refresh();
        $this->assertEquals(CorrectionRequest::STATUS_APPROVED, $correction->status);

        $response = $this->actingAs($user)
            ->get(route('staff.attendance.correction.list', ['tab' => CorrectionRequest::STATUS_APPROVED]));

        $this->assertCorrectionVisible($response, $correction, '承認済み');
    }

    public function test_申請詳細画面に遷移できる()
    {
        [$user, $attendance, $correction] = $this->createPendingRequest();

        $response = $this->actingAs($user)
            ->get(route('staff.attendance.detail', ['id' => $attendance->id]));

        $response->assertSee($attendance->date->isoFormat('YYYY年'));
        $response->assertSee($attendance->date->isoFormat('M月D日'));
    }
}

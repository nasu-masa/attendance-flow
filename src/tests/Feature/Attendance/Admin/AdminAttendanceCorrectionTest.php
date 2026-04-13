<?php

namespace Tests\Feature\Attendance\Admin;

use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\CorrectionRequest;
use Illuminate\Support\Str;

class AdminAttendanceCorrectionTest extends BaseAdminAttendanceTestCase
{
    protected string $listUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listUrl = route('admin.attendance.correction.list');
    }

    protected function detailUrl($attendance_correct_request_id): string
    {
        return route('admin.attendance.correction.approve.show', $attendance_correct_request_id);
    }

    protected function assertListDisplaysRequests(string $status, string $statusLabel)
    {
        $user = $this->makeStaff();

        $attendance = $this->makeAttendance($user, '2026-04-01');

        $request = CorrectionRequest::factory()
            ->for($user)
            ->for($attendance)
            ->{$status}()
            ->create([
                'remarks'     => '電車遅延のため遅刻',
                'created_at'  => '2026-04-02',
            ]);

        $response = $this->get($this->listUrl . "?tab={$status}");

        $response->assertSee('状態');
        $response->assertSee('名前');
        $response->assertSee('対象日時');
        $response->assertSee('申請理由');
        $response->assertSee('申請日時');

        $response->assertSee($statusLabel);

        $response->assertSee($user->name);

        $response->assertSee('2026/04/01');

        $response->assertSee(Str::limit($request->remarks, 10));

        $response->assertSee('2026/04/02');
    }

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $this->assertListDisplaysRequests(CorrectionRequest::STATUS_PENDING, '承認待ち');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $this->assertListDisplaysRequests(CorrectionRequest::STATUS_APPROVED, '承認済み');
    }

    public function test_修正申請の詳細画面が正しく表示されている()
    {
        $attendance = Attendance::factory()->create([
            'date' => '2026-03-25',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => '12:00',
            'break_end'     => '13:00',
        ]);

        $request = CorrectionRequest::factory()->for($attendance)->create([
            'after_value' => [
                'clock_in'  => '09:00',
                'clock_out' => '20:00',
                'remarks'   => '会議のため',
                'breaks'    => [
                    ['start' => '12:00', 'end' => '13:00']
                ],

            ],
            'status' => CorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->get($this->detailUrl($request->id));

        $response->assertSee($attendance->user->name);
        $response->assertSee('2026年');
        $response->assertSee('3月25日');

        $response->assertSee('09:00');
        $response->assertSee('20:00');

        $response->assertSee('12:00');
        $response->assertSee('13:00');

        $response->assertSee('会議のため');

        $response->assertSee('承認');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $user = $this->makeStaff();
        $attendance = Attendance::factory()->for($user)->create();

        $requestToApprove = CorrectionRequest::factory()
            ->for($user)
            ->for($attendance)
            ->pending()
            ->create([
                'after_value' => [
                    'clock_in'  => '09:00',
                    'clock_out' => '18:00',
                    'breaks'    => [
                        ['start' => '12:00', 'end' => '13:00']
                    ],
                    'remarks'   => '修正後の備考',
                ],
            ]);

        $response = $this->patch(route('admin.attendance.correction.approve', $requestToApprove->id));

        $response->assertStatus(302);

        $this->assertDatabaseHas('correction_requests', [
            'id' => $requestToApprove->id,
            'status' => CorrectionRequest::STATUS_APPROVED,
        ]);
    }
}

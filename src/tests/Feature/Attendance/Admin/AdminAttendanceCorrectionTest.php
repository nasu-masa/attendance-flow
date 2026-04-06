<?php

namespace Tests\Feature\Attendance\Admin;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Support\Str;

class AdminAttendanceCorrectionTest extends BaseAdminAttendanceTestCase
{
    protected string $listUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listUrl = route('admin.request.list');
    }

    protected function detailUrl($id): string
    {
        return route('admin.request.approve.show', $id);
    }

    protected function assertListDisplaysRequests(string $status, string $statusLabel)
    {
        // スタッフ作成
        $user = $this->makeStaff();

        // 勤怠作成
        $attendance = $this->makeAttendance($user, '2026-04-01');

        // 修正申請作成
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

        // 状態
        $response->assertSee($statusLabel);

        // 名前
        $response->assertSee($user->name);

        // 対象日時
        $response->assertSee('2026/04/01');

        // 申請理由（10文字省略）
        $response->assertSee(Str::limit($request->remarks, 10));

        // 申請日時
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

        $request = CorrectionRequest::factory()->for($attendance)->create([
            'remarks' => '会議のため',
            'after_value' => [
                'clock_in'       => '09:00',
                'clock_out'      => '20:00',
                'break_start_1'  => '12:00',
                'break_end_1'    => '13:00',
                'break_start_2'  => '15:00',
                'break_end_2'    => '15:15',
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

        $response->assertSee('15:00');
        $response->assertSee('15:15');

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
            ->create();

        $this->patch(route('admin.request.approve', $requestToApprove->id));

        $this->assertDatabaseHas('correction_requests', [
            'id' => $requestToApprove->id,
            'status' => CorrectionRequest::STATUS_APPROVED,
        ]);
    }
}

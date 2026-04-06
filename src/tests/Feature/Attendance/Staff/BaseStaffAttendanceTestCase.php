<?php

namespace Tests\Feature\Attendance\Staff;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

abstract class BaseStaffAttendanceTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Carbon $today;
    protected Carbon $baseDate;

    protected function setUp(): void
    {
        parent::setUp();

        // ログインユーザー
        $this->user = User::factory()->staff()->create();
        $this->actingAs($this->user);

        // Carbon で保持（string にしない）
        $this->today = today();
        $this->baseDate = now()->startOfMonth();
    }

    /**
     * 修正申請テスト用：空の勤怠を作成
     */
    protected function makeEmptyAttendance($date)
    {
        return Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date'    => $date,
            'status'  => Attendance::STATUS_OUT,
        ]);
    }

    /**
     * 一覧・詳細テスト用：フル勤怠を作成
     */
    protected function makeFullAttendance($date)
    {
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'date'      => $date,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => '12:00',
            'break_end'     => '13:00',
        ]);

        return $attendance;
    }

    /**
     * 勤怠一覧・詳細で共通の表示チェック
     */
    protected function assertAttendanceRow($response)
    {
        $response->assertSee('09:00'); // 出勤
        $response->assertSee('18:00'); // 退勤
        $response->assertSee('1:00');  // 休憩
        $response->assertSee('8:00');  // 合計
    }

    /**
     * 出勤開始
     */
    protected function startWork()
    {
        return $this->post(route('staff.attendance.action'),
            ['action' => Attendance::ACTION_START]);
    }

    /**
     * 退勤
     */
    protected function finishWork()
    {
        return $this->post(route('staff.attendance.action'),
            ['action' => Attendance::ACTION_FINISH]);
    }
}

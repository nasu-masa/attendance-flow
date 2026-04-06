<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /* ================================
        今日の勤怠取得（存在しない場合は例外）
    ================================= */

    private function currentOrFail(int $userId): Attendance
    {
        return Attendance::where('user_id', $userId)
            ->where('date', today()->toDateString())
            ->firstOrFail();
    }

    /* ================================
        今日の勤怠取得（存在しない場合は新規）
    ================================= */

    public function getOrInitTodayAttendance(int $userId): Attendance
    {
        return Attendance::firstOrNew(
            ['user_id' => $userId, 'date' => today()->toDateString()],
            ['status'  => Attendance::STATUS_OUT]
        );
    }

    /* ================================
        出勤
    ================================= */

    public function startWork(int $userId)
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $userId,
                'date'    => today()->toDateString(),
            ],
            [
                'status'   => Attendance::STATUS_WORKING,
                'clock_in' => now(),
            ]
        );

        // ボタン連打やプラウザバックによる出勤時間の上書き防止
        if ($attendance->status !== Attendance::STATUS_WORKING) {
            $attendance->update([
                'status'   => Attendance::STATUS_WORKING,
                'clock_in' => now(),
            ]);
        }
        return $attendance;
    }

    /* ================================
        休憩開始
    ================================= */

    public function breakIn(int $userId)
    {
        $attendance = $this->currentOrFail($userId);
        $attendance->update(['status' => Attendance::STATUS_BREAK]);

        BreakLog::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now(),
        ]);

        return $attendance;
    }

    /* ================================
        休憩終了
    ================================= */

    public function breakOut(int $userId)
    {
        $attendance = $this->currentOrFail($userId);
        $attendance->update(['status' => Attendance::STATUS_WORKING]);

        $breakLog = BreakLog::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->orderBy('id')
            ->first();

        if ($breakLog) {
            $breakLog->update(['break_end' => now()]);
        }

        return $attendance;
    }

    /* ================================
        退勤
    ================================= */

    public function finishWork(int $userId)
    {
        $attendance = $this->currentOrFail($userId);

        $attendance->update([
            'status'    => Attendance::STATUS_FINISHED,
            'clock_out' => now(),
        ]);

        return $attendance;
    }

    /* ================================
        月次勤怠取得
    ================================= */

    public function getMonthlyAttendance($userId, $year, $month)
    {
        return Attendance::where('user_id', $userId)
            ->ofMonth($year, $month)
            ->orderBy('date')
            ->with('breakLogs')
            ->get();
    }

    /* ================================
        月次勤怠取得（個人）
        ================================= */

    public function getDailyAttendance(Carbon $date)
    {
        return Attendance::whereDate('date', $date->toDateString())
            ->with('user')
            ->get();
    }

    /* ================================
        勤怠修正
    ================================= */

    public function correctAttendance($id, $request)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remarks'   => $request->remarks,
        ]);

        $break1 = $attendance->breakLogs()->orderBy('id')->first();
        if ($break1) {
            $break1->update([
                'break_start' => $request->break_start_1,
                'break_end'   => $request->break_end_1,
            ]);
        }

        $break2 = $attendance->breakLogs()->orderBy('id')->skip(1)->first();
        if ($break2) {
            $break2->update([
                'break_start' => $request->break_start_2,
                'break_end'   => $request->break_end_2,
            ]);
        }
    }

    /* ================================
        月次勤怠取得（CSV用）
    ================================= */

    public function getMonthlyAttendanceForCsv($userId, $year, $month)
    {
        $user = User::findOrFail($userId);

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        return [$user, $attendances, $year, $month];

    }
}

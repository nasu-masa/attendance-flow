<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Yasumi\Yasumi;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\BreakLog;

class AttendanceSeeder extends Seeder
{

    private function isAbsentDay(Carbon $date)
    {
        return Attendance::whereDate('date', $date->toDateString())
            ->where('status', 'absent')
            ->exists();
    }

    private function nextBusinessDay(Carbon $date)
    {
        $holidays = Yasumi::create('Japan', $date->year);

        $next = $date->copy()->addDay();

        while (
            $next->isWeekend() ||
            $holidays->isHoliday($next) ||
            $this->isAbsentDay($next)
        ) {
            $next->addDay();
        }

        return $next;
    }

    private function makeBeforeValue(Attendance $attendance)
    {
        $break1 = $attendance->breakLogs->get(0);
        $break2 = $attendance->breakLogs->get(1);

        return [
            'clock_in'      => optional($attendance->clock_in)->format('H:i'),
            'clock_out'     => optional($attendance->clock_out)->format('H:i'),
            'break_start_1' => optional($break1)->break_start?->format('H:i'),
            'break_end_1'   => optional($break1)->break_end?->format('H:i'),
            'break_start_2' => optional($break2)->break_start?->format('H:i'),
            'break_end_2'   => optional($break2)->break_end?->format('H:i'),
            'remarks'       => $attendance->remarks,
        ];
    }

    public function run()
    {
        $reasons = require database_path('data/attendance_reasons.php');

        $staffIds = User::staff()->pluck('id');

        foreach ($staffIds as $id) {

            $start = Carbon::create(2026, 1, 1);
            $end   = Carbon::today();

            $holidays = Yasumi::create('Japan', 2026);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

                if ($id === 2 && $date->isToday()) {
                    continue;
                }

                $rand = rand(1, 100);

                $status   = null;
                $clockIn  = null;
                $clockOut = null;
                $remarks  = null;

                if ($date->isWeekend() || $holidays->isHoliday($date)) {
                    continue;
                }

                elseif ($rand <= 3) {
                    $status  = 'absent';
                    $remarks = $reasons['absent'][array_rand($reasons['absent'])];
                }

                elseif ($rand <= 88) {
                    $status   = 'normal';
                    $clockIn  = Carbon::parse($date->toDateString() . ' 09:00');
                    $clockOut = Carbon::parse($date->toDateString() . ' 18:00');
                }

                elseif ($rand <= 93) {
                    $status   = 'late';
                    $lateMin  = [15, 30, 45, 60][array_rand([15, 30, 45, 60])];
                    $clockIn  = Carbon::parse($date->toDateString() . ' 10:00')->addMinutes($lateMin);
                    $clockOut = Carbon::parse($date->toDateString() . ' 18:00');
                    $remarks  = $reasons['clock_in'][array_rand($reasons['clock_in'])];
                }

                elseif ($rand <= 96) {
                    $status   = 'early_leave_morning';
                    $clockIn  = Carbon::parse($date->toDateString() . ' 09:00');
                    $clockOut = Carbon::parse($date->toDateString() . ' 12:00');
                    $remarks  = $reasons['clock_out'][array_rand($reasons['clock_out'])];
                }

                elseif ($rand <= 99) {
                    $status   = 'early_leave_afternoon';
                    $clockIn  = Carbon::parse($date->toDateString() . ' 09:00');
                    $clockOut = Carbon::parse($date->toDateString() . ' 15:00');
                    $remarks  = $reasons['clock_out'][array_rand($reasons['clock_out'])];
                }

                else {
                    $status   = 'afternoon_work';
                    $clockIn  = Carbon::parse($date->toDateString() . ' 13:00');
                    $clockOut = Carbon::parse($date->toDateString() . ' 18:00');
                }

                $attendance = Attendance::create([
                    'user_id'   => $id,
                    'date'      => $date->toDateString(),
                    'status'    => $status,
                    'clock_in'  => $clockIn,
                    'clock_out' => $clockOut,
                    'remarks'   => $remarks
                ]);

                if (in_array($status, ['normal', 'late', 'early_leave_afternoon'])) {

                    $breakStart = Carbon::parse($date->toDateString() . ' 12:00')->addMinutes(rand(0, 10));
                    $breakEnd   = $breakStart->copy()->addHour();

                    BreakLog::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $breakStart,
                        'break_end'     => $breakEnd
                    ]);

                    if (rand(1, 100) <= 3) {

                        $breakStart2 = Carbon::parse($date->toDateString() . ' 15:00')->addMinutes(rand(0, 10));
                        $breakEnd2   = $breakStart2->copy()->addMinutes(15);

                        BreakLog::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $breakStart2,
                            'break_end'     => $breakEnd2
                        ]);
                    }
                }


                if (in_array($status, ['normal', 'late']) && rand(1, 100) <= 10) {

                    $attendance->update([
                        'clock_out' => $attendance->clock_out->copy()->addHour(),
                    ]);

                    if (rand(1, 100) <= 70) {
                        $attendance->update([
                            'remarks' => $reasons['overtime'][array_rand($reasons['overtime'])],
                        ]);
                    }
                }
            }
        }

        $loginUserId = 2;

        $attendances = Attendance::where('user_id', $loginUserId)
            ->whereDate('date', '<', today())
            ->orderBy('date', 'desc')
            ->get();

        $pendingTargets = $attendances->skip(1)->take(3);

        $approvedTargets = $attendances->skip(4)->take(3);

        /* ======================
            pending（3件）
            【理由】UI の動作確認用に、意図的に pending の申請を 3 件だけ生成するため。
            【制約】対象はログインユーザー（ID=2）の過去データに限定。
            【注意】skip/take の件数を変更すると UI の確認データが変わる。
        ====================== */
        foreach ($pendingTargets as $attendance) {

            if (!in_array($attendance->status, ['normal', 'late', 'early_leave_afternoon'])) {
                continue;
            }

            $before = $this->makeBeforeValue($attendance);

            $after = $before;
            $after['clock_in'] = '09:30';
            $after['remarks']  = '電車遅延のため';

            $requestDate = $this->nextBusinessDay(Carbon::parse($attendance->date));

            CorrectionRequest::create([
                'user_id'       => $loginUserId,
                'attendance_id' => $attendance->id,
                'request_type'  => 'clock_in',
                'status'        => CorrectionRequest::STATUS_PENDING,
                'remarks'       => '電車遅延のため',
                'before_value'  => $before,
                'after_value'   => $after,
                'created_at'    => $requestDate->format('Y-m-d H:i:s'),
            ]);
        }

        /* ======================
            approved（3件）
            【理由】承認済み一覧の UI 確認用に、意図的に approved を 3 件生成するため。
            【制約】対象はログインユーザー（ID=2）の過去データに限定。
            【注意】skip/take の件数は UI の確認目的に合わせて調整している。
        ====================== */
        foreach ($approvedTargets as $attendance) {

            if (!in_array($attendance->status, ['normal', 'late', 'early_leave_afternoon'])) {
                continue;
            }

            $before = $this->makeBeforeValue($attendance);

            $after = $before;

            $break_end_2 = $before['break_end_2']
                ? Carbon::parse($before['break_end_2'])
                : null;

            if ($break_end_2) {
                $after['clock_out'] = '16:00';
            } else {
                $after['clock_out'] = '14:00';
            }

            $after['remarks']   = '体調不良のため早退';

            $requestDate = $this->nextBusinessDay(Carbon::parse($attendance->date));

            CorrectionRequest::create([
                'user_id'       => $loginUserId,
                'attendance_id' => $attendance->id,
                'request_type'  => 'clock_out',
                'status'        => CorrectionRequest::STATUS_APPROVED,
                'remarks'       => '体調不良のため早退',
                'before_value'  => $before,
                'after_value'   => $after,
                'created_at'    => $requestDate->format('Y-m-d H:i:s'),
            ])->fresh();
        }
    }
}
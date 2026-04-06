<?php

namespace App\Presenters;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarPresenter extends BasePresenter
{
    /* ================================
        日ナビゲーション
    ================================= */

    public function getDayNavigation(string $date)
    {
        $current = Carbon::parse($date);

        return [
            'current' => $current,
            'prev'    => $current->copy()->subDay(),
            'next'    => $current->copy()->addDay(),
        ];
    }

    /* ================================
        月ナビゲーション
    ================================= */

    public function getMonthNavigation(int $year, int $month)
    {
        $current = Carbon::create($year, $month, 1);

        return [
            'current' => $current,
            'prev'    => $current->copy()->subMonth(),
            'next'    => $current->copy()->addMonth(),
        ];
    }

    /* ================================
        月次カレンダー
        - 実データが無い日は空の Attendance を作る
    ================================= */

    public function getMonthlyCalendar(Collection $attendances, int $year, int $month)
    {
        $attendances = $attendances->keyBy(fn ($attendance) => $attendance->date->toDateString());

        $current = Carbon::create($year, $month, 1);
        $days = [];

        for ($i = 1; $i <= $current->daysInMonth; $i++) {

            $date = $current->copy()->day($i)->toDateString();

            $attendance = $attendances[$date] ?? new Attendance(['date' => $date]);

            $days[$date] = $attendance->setAttribute('is_empty', !isset($attendances[$date]));
        }

        return $days;
    }

    /* ================================
        日次カレンダー（全スタッフ）
        - 実データが無い場合は空の Attendance を作る
    ================================= */

    public function buildDailyCalendar(Collection $users, Collection $attendances, Carbon $date)
    {
        $attendances = $attendances->keyBy('user_id');

        $result = [];

        foreach ($users as $user) {

            $attendance = $attendances[$user->id] ?? new Attendance([
                'user_id' => $user->id,
                'date'    => $date->toDateString(),
            ]);

            $attendance->setRelation('user', $user);

            $presenter = new AttendancePresenter($attendance);

            $result[] = [
                'id'        => $attendance->id,
                'user_id'   => $user->id,
                'name'      => $user->name,
                'is_empty'  => !isset($attendances[$user->id]),

                'clock_in'  => self::resolveValue('clock_in', [], $attendance, 'H:i'),
                'clock_out' => self::resolveValue('clock_out', [], $attendance, 'H:i'),

                'break'     => $presenter->breakTime(),
                'total'     => $presenter->workTime(),
            ];
        }

        return $result;
    }
}
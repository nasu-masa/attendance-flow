<?php

namespace App\Presenters;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarPresenter extends BasePresenter
{
    /**
     * 【理由】日付操作の基準を Carbon に統一し、文字列形式の違いによるズレを防ぐため。
     * 【制約】引数は Carbon が解釈可能な日付文字列である必要がある。
     * 【注意】copy() を使わないと current を破壊してしまい、前後日の計算が誤る可能性がある。
     */
    public function getDayNavigation(string $date)
    {
        $current = Carbon::parse($date);

        return [
            'current' => $current,
            'prev'    => $current->copy()->subDay(),
            'next'    => $current->copy()->addDay(),
        ];
    }

    /**
     * 【理由】現在月・前月・翌月を一貫した形式で返し、月移動 UI の前提を崩さないため。
     * 【制約】year・month が Carbon によって正しく日付生成できる値である必要がある。
     * 【注意】月末処理の影響で日付がずれる可能性があるため、Carbon の仕様に依存する点に注意。
     */
    public function getMonthNavigation(int $year, int $month)
    {
        $current = Carbon::create($year, $month, 1);

        return [
            'current' => $current,
            'prev'    => $current->copy()->subMonth(),
            'next'    => $current->copy()->addMonth(),
        ];
    }

    /**
     * 【理由】月内の全日付を網羅した構造を作り、欠損日の扱いを統一するため。
     * 【制約】year・month が有効な日付として Carbon に解釈できる必要がある。
     * 【注意】勤怠が存在しない日は仮オブジェクトを生成するため、実データとの区別が必要。
     */
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

    /**
     * 【理由】勤怠が存在しないユーザーにも空の Attendance を生成し、一覧表示の欠損を防ぐため。
     * 【制約】users と attendances は user_id をキーに対応している前提で処理される。
     * 【注意】ユーザー数が多い場合、ループ処理と keyBy によるメモリ使用量が増える点に注意。
     */
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
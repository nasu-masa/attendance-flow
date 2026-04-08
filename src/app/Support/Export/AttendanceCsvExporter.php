<?php

namespace App\Support\Export;

use App\Models\User;
use App\Presenters\AttendancePresenter;
use App\Presenters\BasePresenter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceCsvExporter
{
    /**
     * 【理由】CSV 出力形式を統一し、ストリーム方式で大規模データでも安定してダウンロードできるようにするため。
     * 【制約】$user・$attendances・$year・$month が正しい組み合わせであり、勤怠データが日付順で渡されている必要がある。
     * 【注意】ストリーム出力中に例外が発生すると部分的な CSV が生成される可能性があり、呼び出し側での再送処理ができない点に注意。
     */
    public function export(User $user, $attendances, $year, $month)
    {
        $fileName = "{$user->name}_{$year}_{$month}_attendance.csv";

        return new StreamedResponse(function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {

                $presenter = new AttendancePresenter($attendance);

                fputcsv($stream, [
                    $attendance->date?->format('Y-m-d'),
                    BasePresenter::formatTime($attendance->clock_in),
                    BasePresenter::formatTime($attendance->clock_out),
                    $presenter->breakTime(),
                    $presenter->workTime(),
                ]);
            }

            fclose($stream);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
    }
}

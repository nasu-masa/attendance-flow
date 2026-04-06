<?php

namespace App\Support\Export;

use App\Models\User;
use App\Presenters\AttendancePresenter;
use App\Presenters\BasePresenter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceCsvExporter
{
    /* ================================
        Export CSV
    ================================= */

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

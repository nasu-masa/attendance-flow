<?php

namespace App\UseCases;

use App\Models\User;
use App\Presenters\AttendanceListPresenter;
use App\Presenters\CalendarPresenter;
use App\Services\AttendanceService;
use App\Support\Url\CsvExportUrl;

class MonthlyAttendanceUseCase
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected CalendarPresenter $calendarPresenter,
    ) {}

    /* ================================
        月次勤怠データの構築
    ================================= */

    public function buildMonthlyStaffAttendance(User $user, int $year, int $month)
    {
        $nav  = $this->calendarPresenter->getMonthNavigation($year, $month);

        $attendances = $this->attendanceService->getMonthlyAttendance($user->id, $year, $month);

        $days = $this->calendarPresenter->getMonthlyCalendar($attendances, $year, $month);

        $display = AttendanceListPresenter::make($nav, $days);

        $csvUrl = CsvExportUrl::make($user->id, $nav['current']);

        return compact('nav', 'days', 'display', 'csvUrl');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CorrectAttendanceRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Support\Export\AttendanceCsvExporter;
use App\Presenters\AdminDailyAttendanceListPresenter;
use App\Presenters\AttendanceDetailPresenter;
use App\Presenters\CalendarPresenter;
use App\UseCases\MonthlyAttendanceUseCase;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /* ================================
        PG08：勤怠一覧（管理者）
    ================================= */

    public function list(
        Request $request,
        CalendarPresenter $presenter,
        AttendanceService $service
    ) {
        $date = Carbon::parse($request->query('date', today()->toDateString()));

        $nav = $presenter->getDayNavigation($date->toDateString());

        $users = User::staff()->get();
        $attendances = $service->getDailyAttendance($nav['current']);

        $days = $presenter->buildDailyCalendar($users, $attendances, $nav['current']);

        $display = AdminDailyAttendanceListPresenter::make($nav, $days);

        return view('admin.attendance.list', compact('display'));
    }

    /* ================================
        PG09：勤怠詳細（管理者）
    ================================= */

    public function detail($attendanceId)
    {
        $attendance = Attendance::withRelationsForDetails()->findOrFail($attendanceId);

        $display = AttendanceDetailPresenter::make($attendance, true);

        return view('admin.attendance.detail', compact('attendance', 'display'));
    }

    /* ================================
        PG09：勤怠修正（管理者）
    ================================= */

    public function correction(
        CorrectAttendanceRequest $request,
        AttendanceService $service,
        $id
    ) {
        try {
            $service->correctAttendance(
                $id,
                $request
            );
        } catch (\Exception $errorMessage) {
            return back()
                ->withErrors(['no_change' => $errorMessage->getMessage()])
                ->withInput();
        }

        return back()->with('success', '勤怠を修正しました');
    }

    /* ================================
        PG10：スタッフ一覧（管理者）
    ================================= */

    public function staffList()
    {
        $users = User::staff()->get();

        return view('admin.staff.list', compact('users'));
    }

    /* ================================
        PG11：スタッフ別勤怠一覧（管理者）
    ================================= */

    public function staffAttendance(
        Request $request,
        MonthlyAttendanceUseCase $case,
        $id
    ) {
        $user = User::staff()->where('id', $id)->firstOrFail();

        $year  = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $result = $case->buildMonthlyStaffAttendance($user, $year, $month);

        return view('admin.staff.attendance', [
            'user'    => $user,
            'display' => $result['display'],
            'csvUrl'  => $result['csvUrl'],
        ]);
    }

    /* ================================
        PG12：CSV エクスポート（管理者）
    ================================= */

    public function exportCsv(
        AttendanceService $service,
        AttendanceCsvExporter $exporter,
        Request $request, $userId
    )
    {
        [$user, $attendances, $year, $month] = $service->getMonthlyAttendanceForCsv(
            $userId, $request->year, $request->month
        );

        return $exporter->export(
            $user, $attendances, $year, $month
        );
    }
}

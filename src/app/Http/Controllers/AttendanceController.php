<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Presenters\AttendancePresenter;
use App\Presenters\AttendanceDetailPresenter;
use App\Presenters\AttendanceListPresenter;
use App\Presenters\CalendarPresenter;
use App\Presenters\UIState\AttendanceUIState;
use App\Presenters\WorkMessagePresenter;

class AttendanceController extends Controller
{
    /* ================================
        PG03：勤怠登録画面
    ================================= */

    public function index(AttendanceService $service)
    {
        $userId = auth()->id();

        $attendance = $service->getOrInitTodayAttendance($userId);

        $statusLabels = new AttendancePresenter($attendance);
        $display      = new AttendanceUIState($attendance);

        return view('staff.attendance.index', [
            'attendance'   => $attendance,
            'statusLabels' => $statusLabels,
            'display'      => $display,
            'today'        => now(),
            'time'         => now()->format('H:i'),
        ]);
    }

    /* ================================
        勤怠アクション
    ================================= */

    public function action(
        Request $request,
        AttendanceUIState $uiState,
        AttendanceService $service,
        WorkMessagePresenter $presenter
        )
    {
        $action = $request->input('action');
        $userId = auth()->id();

        match ($action) {
            $uiState->startAction()    => $service->startWork($userId),
            $uiState->breakInAction()  => $service->breakIn($userId),
            $uiState->breakOutAction() => $service->breakOut($userId),
            $uiState->finishAction()   => $service->finishWork($userId),
            default => null,
        };

        $message = $presenter->handleAction($action, $uiState);

        if ($message) {
            session()->flash('greeting', $message);
        }

        return redirect()->route('staff.attendance.index')->with('message', $message);
    }

    /* ================================
        PG04：勤怠一覧画面
    ================================= */

    public function list(
        Request $request,
        AttendanceService $service,
        CalendarPresenter $presenter
    ) {
        $year  = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $userId = auth()->id();

        $attendances = $service->getMonthlyAttendance($userId, $year, $month);

        $days = $presenter->getMonthlyCalendar($attendances, $year, $month);
        $nav  = $presenter->getMonthNavigation($year, $month);

        $display = AttendanceListPresenter::make($nav, $days);

        return view('staff.attendance.list', compact('display'));
    }

    /* ================================
        PG05：勤怠(申請)詳細画面
    ================================= */

    public function detail($attendanceId)
    {
        $userId = auth()->id();

        $attendance = Attendance::withRelationsForDetails()
            ->where('id', $attendanceId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $display = AttendanceDetailPresenter::make($attendance);

        return view('staff.attendance.detail', compact('attendance', 'display'));
    }
}

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
    /**
     * 【理由】ログイン中スタッフの今日の勤怠データを取得または初期化し、表示に必要な Presenter / UIState を構築するため。
     * 【制約】auth()->id() が有効なスタッフユーザーであること、AttendanceService が正しく動作することが前提となる。
     * 【注意】未保存の Attendance が返る場合があるため、ビュー側では保存前提の処理を行わないようにする必要がある。
     */
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
        ]);
    }

    /**
     * 【理由】UIState のアクション名を基準に勤怠処理とメッセージ生成を一元的に振り分けるため。
     * 【制約】リクエストの action が UIState の定数と一致し、ユーザーが認証済みである必要がある。
     * 【注意】未知の action は無視されるため、UIState の定数変更時は Presenter・Service と同期が必要。
     */
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

    /**
     * 【理由】指定年月の勤怠データとカレンダー情報を統合し、一覧表示に必要な構造へ整えるため。
     * 【制約】ユーザーが認証済みであり、year・month が数値として解釈できることを前提とする。
     * 【注意】年月が不正でも現在年月で処理されるため、意図しない月が表示される可能性に注意。
     */
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

    /**
     * 【理由】ログイン中ユーザー自身の勤怠詳細のみを安全に取得し、表示に必要な情報を揃えるため。
     * 【制約】attendanceId が本人のデータとして存在し、関連情報が取得可能であることを前提とする。
     * 【注意】他人の ID や欠損 ID が指定された場合は例外が発生し、画面遷移が中断される点に注意。
     */
    public function detail(int $attendanceId)
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

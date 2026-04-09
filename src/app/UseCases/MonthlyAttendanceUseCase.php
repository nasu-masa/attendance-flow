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

    /**
     * 【理由】月次勤怠表示に必要なナビゲーション・勤怠データ・カレンダー構造・CSV URL を一括で構築し、呼び出し側の処理を単純化するため。
     * 【制約】$user は有効なスタッフユーザーであり、year/month は存在する月として扱える値である必要がある。
     * 【注意】勤怠データが欠損している場合や月の境界処理は Presenter／Service に依存するため、戻り値の整合性はそれらの実装に左右される。
     */
    public function buildMonthlyStaffAttendance(User $user, int $year, int $month)
    {
        $nav  = $this->calendarPresenter->getMonthNavigation($year, $month);

        $attendances = $this->attendanceService->getMonthlyAttendance($user->id, $year, $month);

        $days = $this->calendarPresenter->getMonthlyCalendar($attendances, $year, $month);

        $attendanceList = AttendanceListPresenter::make($nav, $days);

        $csvUrl = CsvExportUrl::make($user->id, $nav['current']);

        return compact('nav', 'days', 'attendanceList', 'csvUrl');
    }
}

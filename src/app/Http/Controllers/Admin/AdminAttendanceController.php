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
    /**
     * 【理由】日付入力が欠けても today() を基準に処理できるよう、常に Carbon に正規化している。
     * 【制約】date クエリは日付文字列であることを前提としており、不正形式の場合は Carbon が例外を投げる。
     * 【注意】全ユーザー分の勤怠を扱うため、ユーザー数が多い環境ではクエリ負荷とメモリ使用量に注意が必要。
     */
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


    /**
     * 【理由】勤怠詳細に必要な関連データを一度に取得し、表示時の追加クエリを避けるため。
     * 【制約】attendanceId はログインユーザーの権限で参照可能なレコードである必要がある。
     * 【注意】関連が欠けている場合でも withRelationsForDetails が null を返すため、view 側での null ハンドリングが必要。
     */
    public function detail(int $attendanceId)
    {
        $attendance = Attendance::withRelationsForDetails()->findOrFail($attendanceId);

        $display = AttendanceDetailPresenter::make($attendance, true);

        return view('admin.attendance.detail', compact('attendance', 'display'));
    }


    /**
     * 【理由】勤怠修正処理を実行し、成功時と失敗時のユーザーへのフィードバックを統一的に扱うため。
     * 【制約】$id は修正対象の勤怠レコードであり、リクエスト内容は CorrectAttendanceRequest によって検証済みである必要がある。
     * 【注意】サービス側で例外が発生した場合は入力値を保持して戻るため、例外メッセージの内容がそのままユーザーに表示される点に注意。
     */
    public function correction(
        CorrectAttendanceRequest $request,
        AttendanceService $service,
        int $id
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


    /**
     * 【理由】スタッフ権限のユーザーのみを一覧表示するため、role=staff の絞り込み結果をビューに渡している。
     * 【制約】User モデルに staff スコープが正しく定義されていることが前提となる。
     * 【注意】関連データを読み込まないため、大量ユーザー環境では後続の表示処理で追加クエリが発生する可能性がある。
     */
    public function staffList()
    {
        $users = User::staff()->get();

        return view('admin.staff.list', compact('users'));
    }


    /**
     * 【理由】指定ユーザーがスタッフであることを保証した上で、月次勤怠表示に必要な年月とデータ構築処理をまとめて実行するため。
     * 【制約】$id は staff スコープで取得可能なユーザーであり、year/month は整数として扱える値である必要がある。
     * 【注意】年月指定が不正な場合や対象ユーザーに勤怠が存在しない場合でも UseCase 側の戻り値に依存するため、表示側での null ハンドリングが必要。
     */
    public function staffAttendance(
        Request $request,
        MonthlyAttendanceUseCase $case,
        int $id
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


    /**
     * 【理由】CSV 出力に必要な勤怠データをサービス側で一括取得し、出力処理を専用クラスへ委ねるため。
     * 【制約】$userId・$request->year・$request->month が有効な組み合わせであり、サービス側で取得可能である必要がある。
     * 【注意】データ取得時の例外はサービス側に依存するため、Controller では例外処理が行われない点に注意。
     */
    public function exportCsv(
        AttendanceService $service,
        AttendanceCsvExporter $exporter,
        Request $request,
        int $userId
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

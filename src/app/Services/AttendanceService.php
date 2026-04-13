<?php

namespace App\Services;

use App\Http\Requests\Admin\CorrectAttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * 【理由】当日の勤怠が必ず存在する前提を強制し、後続処理の整合性崩壊を防ぐため。
     * 【制約】当日の勤怠が1件だけ存在することを前提としており、欠損や重複は想定していない。
     * 【注意】today() 基準のため日付跨ぎやタイムゾーン差異があると意図しない例外が発生する可能性がある。
     */
    private function currentOrFail(int $userId): Attendance
    {
        return Attendance::where('user_id', $userId)
            ->where('date', today()->toDateString())
            ->firstOrFail();
    }

    /**
     * 【理由】今日の勤怠データが存在しない場合でも、初回打刻前の状態として扱えるよう未保存の Attendance を返すため。
     * 【制約】user_id と date の組み合わせで勤怠が一意に管理されていることが前提となる。
     * 【注意】firstOrNew により返される Attendance は未保存の可能性があるため、保存前提の処理は呼び出し側で行わないこと。
     */
    public function getOrInitTodayAttendance(int $userId): Attendance
    {
        return Attendance::firstOrNew(
            ['user_id' => $userId, 'date' => today()->toDateString()],
            ['status'  => Attendance::STATUS_OUT]
        );
    }

    /**
     * 【理由】当日の勤怠が既に存在する可能性を考慮し、出勤状態へ確実に統一するため。
     * 【制約】有効な userId が渡され、当日の勤怠が1件に収まっている前提で動作する。
     * 【注意】過去のステータスが残っている場合でも clock_in を上書きするため、再出勤扱いになる点に注意。
     */
    public function startWork(int $userId)
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $userId,
                'date'    => today()->toDateString(),
            ],
            [
                'status'   => Attendance::STATUS_WORKING,
                'clock_in' => now(),
            ]
        );

        if ($attendance->status !== Attendance::STATUS_WORKING) {
            $attendance->update([
                'status'   => Attendance::STATUS_WORKING,
                'clock_in' => now(),
            ]);
        }
        return $attendance;
    }

    /**
     * 【理由】出勤中の勤怠が存在する前提で休憩開始を記録し、後続の休憩終了処理と整合性を保つため。
     * 【制約】未終了の勤怠が1件だけ存在し、休憩開始が重複しない前提で動作する。
     * 【注意】休憩ログは開始時点で必ず1件追加されるため、誤操作でも履歴が残る点に注意。
     */
    public function breakIn(int $userId)
    {
        $attendance = $this->currentOrFail($userId);
        $attendance->update(['status' => Attendance::STATUS_BREAK]);

        BreakLog::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now(),
        ]);

        return $attendance;
    }

    /**
     * 【理由】未終了の休憩ログが1件だけ存在する前提で休憩終了時刻を確定させるため。
     * 【制約】break_start が未完了のログが複数存在しない前提で、最初の1件のみを対象とする。
     * 【注意】休憩ログが異常に複数残っている場合でも補正せず、整合性は呼び出し側に委ねられる。
     */
    public function breakOut(int $userId)
    {
        $attendance = $this->currentOrFail($userId);
        $attendance->update(['status' => Attendance::STATUS_WORKING]);

        $breakLog = BreakLog::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->orderBy('id')
            ->first();

        if ($breakLog) {
            $breakLog->update(['break_end' => now()]);
        }

        return $attendance;
    }

    /**
     * 【理由】勤務終了時刻を確定させ、以降の勤務時間計算の基準を固定するため。
     * 【制約】終了していない当日の勤怠が1件だけ存在する前提で動作する。
     * 【注意】二重終了を防ぐ仕組みは持たず、終了後の再呼び出しは想定していない。
     */
    public function finishWork(int $userId)
    {
        $attendance = $this->currentOrFail($userId);

        $attendance->update([
            'status'    => Attendance::STATUS_FINISHED,
            'clock_out' => now(),
        ]);

        return $attendance;
    }

    /**
     * 【理由】指定年月の勤怠データを関連情報込みで取得し、後続処理が追加クエリなしで扱えるようにするため。
     * 【制約】userId・year・month が正しい範囲の値であることを前提とする。
     * 【注意】対象月にデータが存在しない場合でも空コレクションを返すため、呼び出し側で空判定が必要。
     */
    public function getMonthlyAttendance(int $userId, int $year, int $month)
    {
        return Attendance::where('user_id', $userId)
            ->ofMonth($year, $month)
            ->orderBy('date')
            ->with('breakLogs')
            ->get();
    }


    /**
     * 【理由】whereDate を使うことで、時刻を含むデータでも日単位で正確に抽出できるため。
     * 【制約】引数の Carbon は日付として有効である必要があり、toDateString() が正しく生成される前提。
     * 【注意】user リレーションを eager load しないと一覧表示時に N+1 が発生する可能性がある。
     */
    public function getDailyAttendance(Carbon $date)
    {
        return Attendance::whereDate('date', $date->toDateString())
            ->with('user')
            ->get();
    }

    /**
     * 【理由】勤怠と休憩ログの整合性を保ったまま修正後の値を反映し、他機能への影響を最小化するため。
     * 【制約】勤怠レコードと休憩ログの件数・順序が取得可能であることを前提に更新処理を行う。
     * 【注意】存在しない休憩ログは更新対象外となるため、データ欠損時の挙動が状況に依存する点に注意。
     */
    public function correctAttendance(int $id, CorrectAttendanceRequest $request)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remarks'   => $request->remarks,
        ]);

        $breaks = $request->breaks ?? [];

        foreach ($attendance->breakLogs as $index => $breakLog) {
            if (!isset($breaks[$index])) {
                continue;
            }

            $breakLog->update([
                'break_start' => $breaks[$index]['start'],
                'break_end'   => $breaks[$index]['end'],
            ]);
        }
    }

    /**
     * 【理由】CSV 出力に必要なユーザー情報と月次勤怠データをまとめて返し、呼び出し側の処理を簡潔にするため。
     * 【制約】$userId が存在するユーザーであり、year/month が日付として扱える整数である必要がある。
     * 【注意】勤怠データが存在しない場合でも空配列を返すため、出力側での扱いに依存する点に注意。
     */
    public function getMonthlyAttendanceForCsv(int $userId, int $year, int $month)
    {
        $user = User::findOrFail($userId);

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        return [$user, $attendances, $year, $month];

    }
}

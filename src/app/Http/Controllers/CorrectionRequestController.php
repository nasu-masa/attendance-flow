<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Presenters\AttendanceRequestListPresenter;
use App\Http\Requests\Staff\CorrectionRequestRequest;
use App\Services\CorrectionRequestService;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class CorrectionRequestController extends Controller
{
    /**
     * 【理由】対象勤怠の存在を前提に修正申請処理へ委譲し、例外発生時に画面破綻を防ぐため。
     * 【制約】サービス層が差分なし例外を投げる前提で、UI 側に適切なエラーを返す必要がある。
     * 【注意】例外内容をそのまま表示するため、サービス層のメッセージ設計に依存する点に注意。
     */
    public function request(
        CorrectionRequestRequest $request,
        CorrectionRequestService $service,
        int $id
    ) {
        $attendance = Attendance::findOrFail($id);

        try {
            $service->createCorrectionRequest(
                $attendance,
                $request->validated()
            );
        } catch (\Exception $errorMessage) {
            return back()
                ->withErrors(['no_change' => $errorMessage->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('staff.attendance.detail', ['id' => $id])
            ->with('success', '修正申請を送信しました');
    }

    /**
     * 【理由】タブ状態に応じた申請一覧を取得し、ユーザーが必要な情報へ素早くアクセスできるようにするため。
     * 【制約】tab が定義済みステータスであることを前提に、取得対象が正しく切り替わる必要がある。
     * 【注意】取得結果が空の場合でも正常遷移するため、ビュー側での空表示処理が前提となる。
     */
    public function requestList(Request $request, CorrectionRequestService $service)
    {
        $userId = auth()->id();

        $pending = CorrectionRequest::STATUS_PENDING;

        $tab = $request->query('tab', $pending);

        $requests = $tab === $pending
            ? $service->getPendingRequestsByUser($userId)
            : $service->getApprovedRequestsByUser($userId);

        $display = AttendanceRequestListPresenter::make($requests, $tab);

        return view('staff.request.list', compact('display'));
    }
}

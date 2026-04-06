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
    /* ================================
        PG05：修正申請の送信
    ================================= */

    public function request(
        CorrectionRequestRequest $request,
        CorrectionRequestService $service,
        $id
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

    /* ================================
        PG06：申請一覧画面
    ================================= */

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

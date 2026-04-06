<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CorrectionRequestService;
use App\Models\User;
use App\Models\CorrectionRequest;
use App\Presenters\AttendanceRequestListPresenter;
use App\Presenters\CorrectionRequestPresenter;

class CorrectionController extends Controller
{
    /* ================================
        PG12：申請一覧（管理者）
    ================================= */

    public function requestList(Request $request, CorrectionRequestService $service)
    {
        $userIds = User::staff()->pluck('id');

        $tab = $request->query('tab', CorrectionRequest::STATUS_PENDING);

        $requests = ($tab === CorrectionRequest::STATUS_PENDING)
            ? $service->getPendingRequestsByUser($userIds)
            : $service->getApprovedRequestsByUser($userIds);

        $display = AttendanceRequestListPresenter::make($requests, $tab);

        return view('admin.request.list', compact('display'));
    }

    /* ================================
        PG13：修正申請承認画面（管理者）
    ================================= */

    public function showApprove($id)
    {
        $correctionRequest = CorrectionRequest::findOrFail($id);

        $attendance = $correctionRequest->attendance()
            ->withAllRelations()
            ->first();

        $display = CorrectionRequestPresenter::make($correctionRequest);

        return view('admin.request.approve', compact('attendance', 'display'));
    }

    /* ================================
        修正申請の承認（管理者）
    ================================= */

    public function approve(CorrectionRequestService $service, $id)
    {
        $service->approveById($id, auth()->user());

        return back()->with('success', '承認しました');
    }
}

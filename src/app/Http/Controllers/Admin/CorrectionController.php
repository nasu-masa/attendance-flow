<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Presenters\AttendanceRequestListPresenter;
use App\Presenters\CorrectionRequestPresenter;
use App\Services\CorrectionRequestService;
use Illuminate\Http\Request;

class CorrectionController extends Controller
{
    /**
     * 【理由】全スタッフの申請をタブ状態に応じて取得し、一覧表示に必要なデータを統一形式で構築するため。
     * 【制約】staff スコープで取得したユーザーIDが有効であり、tab が定義済みステータスである必要がある。
     * 【注意】取得件数が多い場合はサービス側のクエリ負荷に依存し、表示パフォーマンスが影響を受ける可能性がある。
     */
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


    /**
     * 【理由】修正申請に紐づく勤怠データを全リレーション込みで取得し、承認画面に必要な情報を一度に揃えるため。
     * 【制約】$id は存在する修正申請の ID であり、関連する勤怠データが取得可能である必要がある。
     * 【注意】勤怠データが欠損している場合は null が返るため、Presenter 側の処理に依存して表示が決まる。
     */
    public function showApprove(int $id)
    {
        $correctionRequest = CorrectionRequest::findOrFail($id);

        $attendance = $correctionRequest->attendance()
            ->withAllRelations()
            ->first();

        $display = CorrectionRequestPresenter::make($correctionRequest);

        return view('admin.request.approve', compact('attendance', 'display'));
    }


    /**
     * 【理由】修正申請の承認処理をサービスへ委譲し、承認者（管理者）を明示的に渡すことで記録と権限チェックを統一的に行うため。
     * 【制約】$id は存在する修正申請の ID であり、auth()->user() が管理者であることが前提となる。
     * 【注意】承認処理中の例外はサービス側に依存するため、Controller では成功時のフィードバックのみを扱う。
     */
    public function approve(CorrectionRequestService $service, int $id)
    {
        $service->approveById($id, auth()->user());

        return back()->with('success', '承認しました');
    }
}

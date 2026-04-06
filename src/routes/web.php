<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CorrectionController;
use App\Http\Controllers\CorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =====================================
//  一般ユーザー：認証
// =====================================

Route::post('/register', [AuthController::class, 'store'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('staff.login.post');

// =====================================
//  一般ユーザー：勤怠
// =====================================

Route::middleware(['auth'])->group(function () {

    // 勤務画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('staff.attendance.index');

    // 出勤・退勤・休憩
    Route::post('/attendance', [AttendanceController::class, 'action'])
        ->name('staff.attendance.action');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('staff.attendance.list');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('staff.attendance.detail');

    // 修正申請（POST）
    Route::post('/attendance/detail/{id}', [CorrectionRequestController::class, 'request'])
        ->name('staff.attendance.detail.post');

    // 修正申請一覧
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'requestList'])
        ->name('staff.request.list');

    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


// =====================================
//  管理者：認証
// =====================================

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.post');

    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->name('logout');
});


// =====================================
//  管理者：勤怠
// =====================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // 勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
        ->name('attendance.list');

    // 勤怠詳細
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])
        ->name('attendance.detail');

    // 勤怠修正
    Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'correction'])
        ->name('attendance.correction');

    // スタッフ一覧
    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])
        ->name('staff.list');

    // スタッフ勤怠一覧
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])
        ->name('attendance.staff');

    // CSV 出力
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])
        ->name('attendance.staff.csv');
});


// =====================================
//  管理者：修正申請
// =====================================

Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // 修正申請一覧
    Route::get('/stamp_correction_request/admin/list', [CorrectionController::class, 'requestList'])
        ->name('request.list');

    // 修正申請の承認画面
    Route::get('/stamp_correction_request/approve/{id}', [CorrectionController::class, 'showApprove'])
        ->name('request.approve.show');

    // 修正申請の承認
    Route::patch('/stamp_correction_request/approve/{id}', [CorrectionController::class, 'approve'])
        ->name('request.approve');
});


// =====================================
//  メール認証
// =====================================

// 誘導画面（ログイン後の未認証チェック）
Route::get('/email/verify', function () {
    return view('staff.auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール内リンク → 認証処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('staff.attendance.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メールの再送
Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return redirect()
        ->route('verification.notice');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// =====================================
//  トップページ（認証後は勤怠画面へリダイレクト）
// =====================================

Route::get('/', function () {
    return redirect()->route('staff.attendance.index');
})->middleware('verified');

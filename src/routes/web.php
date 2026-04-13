<?php

use App\Http\Controllers\Staff\AttendanceController;
use App\Http\Controllers\Staff\CorrectionRequestController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

// =====================================
//  一般ユーザー：認証
// =====================================


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.post');
});

// =====================================
//  一般ユーザー：勤怠
// =====================================

Route::middleware(['auth', 'verified', 'staff'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('staff.attendance.index');

    Route::post('/attendance', [AttendanceController::class, 'action'])
        ->name('staff.attendance.action');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('staff.attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('staff.attendance.detail');

    Route::post('/attendance/detail/{id}', [CorrectionRequestController::class, 'request'])
        ->name('staff.attendance.detail.post');

    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'requestList'])
        ->name('staff.attendance.correction.list');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// =====================================
//  メール認証
// =====================================

Route::get('/email/verify', function () {
    return view('staff.auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('staff.attendance.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return redirect()
        ->route('verification.notice');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// ===========================================
//  トップページ（認証後は勤怠画面へリダイレクト）
// ===========================================

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        if ($user->role === \App\Models\User::ROLE_ADMIN) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->route('staff.attendance.index');
    }

    return redirect()->route('login');
});

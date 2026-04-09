<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CorrectionController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// =====================================
//  管理者：認証
// =====================================

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->middleware('guest:admin')
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.post');

    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('logout');
});


// =====================================
//  管理者：勤怠
// =====================================

Route::middleware(['auth:admin', AdminMiddleware::class])->name('admin.')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('attendance.list');

        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])
            ->name('attendance.detail');

        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'correction'])
            ->name('attendance.correction');

        Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])
            ->name('staff.list');

        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])
            ->name('attendance.staff');

        Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])
            ->name('attendance.staff.csv');
    });

    Route::get('/stamp_correction_request/admin/list', [CorrectionController::class, 'requestList'])
        ->name('attendance.correction.list');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [CorrectionController::class, 'showApprove'])
        ->name('attendance.correction.approve.show');

    Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}', [CorrectionController::class, 'approve'])
        ->name('attendance.correction.approve');
});
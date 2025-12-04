<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Staff\WorkController as StaffWorkController;
use App\Http\Controllers\Admin\WorkController as AdminWorkController;
use App\Http\Controllers\Staff\ApplicationController as StaffApplicationController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Illuminate\Support\Facades\Auth;

// 管理者ルート
Route::prefix('admin')->name('admin.')->group(function() {
    // ログイン機能
    Route::get('/login',[AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',[AdminLoginController::class, 'login']);

    // 管理者のスタッフ勤怠確認・更新、スタッフリスト閲覧
    Route::get('/list', [AdminWorkController::class, 'index'])->name('index');
    Route::get('/attendance/{id}', [AdminWorkController::class, 'edit'])->name('edit');
    Route::put('/attendance/{id}', [AdminWorkController::class, 'update'])->name('update');
    Route::get('/staff/list', [AdminWorkController::class, 'staffList'])->name('staffList');
    Route::get('/attendance/staff/{id}', [AdminWorkController::class, 'staffMonthly'])->name('staffMonthly');
    Route::get('/attendance/staff/{id}/csv', [AdminWorkController::class, 'exportMonthly'])->name('staffMonthly.csv');

    // 管理者の修正申請一覧・承認
    Route::name('application.')
        ->group(function () {
            Route::get('/stamp_correction_request/list', [AdminApplicationController::class, 'index'])->name('index');
            Route::get('/stamp_correction_request/detail/{id}', [AdminApplicationController::class, 'show'])->name('show');
            Route::post('/stamp_correction_request/detail/{id}', [AdminApplicationController::class, 'approve'])->name('approve');
        });
});

// 新規会員登録(スタッフのみ)
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// ログイン処理(スタッフ)
Route::get('/login',[StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/login',[StaffLoginController::class, 'login']);

// スタッフの打刻・勤怠一覧・勤怠詳細・修正申請
Route::middleware(['auth:staff', 'verified', 'role:staff'])
    ->prefix('attendance')
    ->name('attendance.')
    ->group(function() {
        Route::get('/', [StaffWorkController::class, 'today'])->name('today');
        Route::post('/clock-in', [StaffWorkController::class, 'clockIn'])->name('clockIn');
        Route::post('/clock-out', [StaffWorkController::class, 'clockOut'])->name('clockOut');
        Route::post('/break-start', [StaffWorkController::class, 'breakStart'])->name('breakStart');
        Route::post('/break-end', [StaffWorkController::class, 'breakEnd'])->name('breakEnd');
        Route::get('/list', [StaffWorkController::class, 'index'])->name('index');
        Route::get('/detail/{id}', [StaffWorkController::class,'edit'])->name('edit');
        Route::post('/detail/{id}', [StaffApplicationController::class, 'store'])->name('application.store');
    });

// スタッフの申請一覧
Route::middleware(['auth', 'verified', 'role:staff'])
    ->name('staff.application.')
    ->group(function() {
        Route::get('/stamp_correction_request/list', [StaffApplicationController::class, 'index'])->name('index');
        Route::get('/stamp_correction_request/detail/{id}',[StaffApplicationController::class, 'show'])->name('show');
    });

Route::post('/logout', function () {
    $user = request()->user();
    Auth::logout();

    return $user && $user->isAdmin()
        ? redirect()->route('admin.login')
        : redirect()->route('staff.login');
})->name('logout');


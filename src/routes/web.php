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

/* 管理者用ルート */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',[AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',[AdminLoginController::class, 'login']);
});

Route::prefix('admin')->middleware(['auth:admin','verified','role:admin'])->name('admin.')->group(function() {
    Route::get('/attendance/list', [AdminWorkController::class, 'index'])->name('index');
    Route::get('/attendance/{id}', [AdminWorkController::class, 'edit'])->name('edit');
    Route::put('/attendance/{id}', [AdminWorkController::class, 'update'])->name('update');
    Route::get('/staff/list', [AdminWorkController::class, 'staffList'])->name('staffList');
    Route::get('/attendance/staff/{id}', [AdminWorkController::class, 'staffMonthly'])->name('staffMonthly');
    Route::get('/attendance/staff/{id}/csv', [AdminWorkController::class, 'exportMonthly'])->name('staffMonthly.csv');

    Route::name('application.')
        ->group(function () {
            Route::get('/stamp_correction_request/list', [AdminApplicationController::class, 'index'])->name('index');
            Route::get('/stamp_correction_request/approve/{id}', [AdminApplicationController::class, 'show'])->name('show');
            Route::post('/stamp_correction_request/approve/{id}', [AdminApplicationController::class, 'approve'])->name('approve');
        });
});


/* スタッフ用ルート */
// 認証関係
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/login',[StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/login',[StaffLoginController::class, 'login']);

Route::middleware(['auth:staff', 'verified', 'role:staff'])
    ->group(function () {

        Route::prefix('attendance')
            ->name('attendance.')
            ->group(function () {
                Route::get('/', [StaffWorkController::class, 'today'])->name('today');
                Route::post('/clock-in', [StaffWorkController::class, 'clockIn'])->name('clockIn');
                Route::post('/clock-out', [StaffWorkController::class, 'clockOut'])->name('clockOut');
                Route::post('/break-start', [StaffWorkController::class, 'breakStart'])->name('breakStart');
                Route::post('/break-end', [StaffWorkController::class, 'breakEnd'])->name('breakEnd');
                Route::get('/list', [StaffWorkController::class, 'index'])->name('index');
                Route::get('/detail/{id}', [StaffWorkController::class,'edit'])->name('edit');
                Route::post('/detail/{id}', [StaffApplicationController::class, 'store'])->name('application.store');
            });

        Route::name('staff.application.')
            ->group(function () {
                Route::get('/stamp_correction_request/list', [StaffApplicationController::class, 'index'])->name('index');
                Route::get('/stamp_correction_request/detail/{id}',[StaffApplicationController::class, 'show'])->name('show');
            });
    });

/* ログアウト処理（管理者・スタッフ共通 */
Route::post('/logout', function () {
    $user = request()->user();
    Auth::logout();

    return $user && $user->isAdmin()
        ? redirect()->route('admin.login')
        : redirect()->route('staff.login');
})->name('logout');

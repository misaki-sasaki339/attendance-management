<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Staff\WorkController as StaffWorkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

// 新規会員登録(スタッフのみ)
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// ログイン処理(スタッフ)
Route::get('/login',[StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/login',[StaffLoginController::class, 'login']);

// ログイン処理(管理者)
Route::prefix('admin')->name('admin.')->group(function() {
    Route::get('/login',[AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',[AdminLoginController::class, 'login']);
});

//ログイン後機能(スタッフ)
Route::middleware(['auth', 'role:staff'])
    ->prefix('attendance')
    ->name('attendance.')
    ->group(function() {
        Route::get('/', [StaffWorkController::class, 'index'])->name('index');
        Route::post('/clock-in', [StaffWorkController::class, 'clockIn'])->name('clockIn');
        Route::post('/clock-out', [StaffWorkController::class, 'clockOut'])->name('clockOut');
        Route::post('/break-start', [StaffWorkController::class, 'breakStart'])->name('breakStart');
        Route::post('break-end', [StaffWorkController::class, 'breakEnd'])->name('breakEnd');
    });


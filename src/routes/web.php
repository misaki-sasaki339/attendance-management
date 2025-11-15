<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Staff\WorkController as StaffWorkController;

// 新規会員登録(スタッフのみ)
Route::get('/register', function() {
    return view('auth.register');
})->name('staff.register');

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
        Route::post('/clock-in', [StaffWorkController::class, 'clockIn'])->name('clockIn');
        Route::post('/clock-out', [StaffWorkController::class, 'clockOut'])->name('clockOut');
    });


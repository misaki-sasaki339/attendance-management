<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Staff\WorkController as StaffWorkController;
use App\Http\Controllers\Admin\WorkController as AdminWorkController;
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
Route::middleware(['auth', 'verified', 'role:staff'])
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
        Route::post('/detail/{id}/store', [StaffWorkController::class, 'store'])->name('store');
    });

// ログイン後機能(管理者)
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function() {
        Route::get('/list', [AdminWorkController::class, 'index'])->name('index');
        Route::get('/attendance/{id}', [AdminWorkController::class, 'edit'])->name('edit');
        Route::put('/attendance/{id}', [AdminWorkController::class, 'update'])->name('update');
    });


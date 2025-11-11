<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StaffLoginController;

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

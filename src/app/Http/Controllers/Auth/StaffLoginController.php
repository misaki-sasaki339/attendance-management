<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffLoginController extends LoginController
{
    protected string $title = 'ログイン';
    protected string $buttonLabel = 'ログインする';
    protected string $route = 'staff.login';
    protected string $role = 'staff';

    /**
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->role);
    }

    protected function username()
    {
        return 'email';
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $request->only($this->username(), 'password'),
            $request->filled('remember')
        );
    }

    protected function authenticated(Request $request, $user)
    {
        // スタッフ用ログインフォームから管理者を弾く
        if ($user->role !== 'staff') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'スタッフアカウントでログインしてください。',
            ]);
        }
        // 未認証の場合はメール認証に飛ばす
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        return redirect()->route('attendance.today');
    }
}

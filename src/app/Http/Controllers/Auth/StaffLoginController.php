<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class StaffLoginController extends LoginController
{
    protected string $title = 'ログイン';
    protected string $buttonLabel = 'ログインする';
    protected string $route = 'staff.login';
    protected string $role = 'staff';

    public function showLoginForm()
    {
        return view('auth.login', [
            'title' => $this->title,
            'buttonLabel' => $this->buttonLabel,
            'route' => $this->route,
            'role' => $this->role,
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        // 未認証の場合はメール認証に飛ばす
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        return redirect()->route('attendance.index');
    }
}

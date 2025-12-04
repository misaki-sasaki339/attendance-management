<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends LoginController
{
    protected string $title = '管理者ログイン';
    protected string $buttonLabel = '管理者ログインする';
    protected string $route = 'admin.login';
    protected string $role = 'admin';

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
        return redirect()->route('admin.index');
    }
}

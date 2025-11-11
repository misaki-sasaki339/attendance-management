<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class AdminLoginController extends LoginController
{
    protected string $title = '管理者ログイン';
    protected string $buttonLabel = '管理者ログインする';
    protected string $route = 'admin.login';
    protected string $role = 'admin';

    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('admin.dashboard');
    }
}

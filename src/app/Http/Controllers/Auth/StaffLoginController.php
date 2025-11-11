<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class StaffLoginController extends LoginController
{
    protected string $title = 'ログイン';
    protected string $buttonLabel = 'ログインする';
    protected string $route = 'staff.login';
    protected string $role = 'staff';

    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('staff.dashboard');
    }
}

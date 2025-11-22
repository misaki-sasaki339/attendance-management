<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    protected string $title;
    protected string $buttonLabel;
    protected string $route;
    protected string $role;

    // ログイン画面の文言を管理者とスタッフとで切り替える
    public function showLoginForm()
    {
        return view('auth.login', [
            'title' => $this->title,
            'buttonLabel' => $this->buttonLabel,
            'route' => $this->route,
            'role' => $this->role,
        ]);
    }

    // ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return $this->authenticated($request, Auth::user());
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    // ログイン後の処理
    protected function authenticated(Request $request, $user)
    {
        return redirect()->intended('/');
    }
}

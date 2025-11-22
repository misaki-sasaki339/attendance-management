<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered;

class StaffRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, CreateNewUser $creator)
    {
        $staff = $creator->create($request->validated());
        event(new Registered($staff));
        return redirect()->route('verification.notice');
    }
}

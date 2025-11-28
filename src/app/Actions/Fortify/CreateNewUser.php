<?php

namespace App\Actions\Fortify;

use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\RegisterRequest;

use Illuminate\Support\Facades\Log;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Staff
    {
        // FormRequestのバリデーション結果を$validatedに格納
        $request = app(RegisterRequest::class);
        $validated = $request->validated();

        $staff =  Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return $staff;
    }
}

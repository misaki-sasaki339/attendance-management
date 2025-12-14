<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Staff;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録成功
    public function test_register_staff()
    {
        $data = [
            'name' => 'テストユーザー',
            'email' => "test@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);
        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas(Staff::class, [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    //会員情報登録--名前バリデーション
    public function test_register_staff_validate_name()
    {
        $data = [
            'name' => '',
            'email' => "test@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    //会員情報登録--メアドバリデーション
    public function test_register_staff_validate_email()
    {
        $data = [
            'name' => 'テストユーザー',
            'email' => "",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    // //会員情報登録--パスワード7文字以下
    public function test_register_user_validate_password_under7()
    {
        $data = [
            'name' => 'テストユーザー',
            'email' => "test@example.com",
            'password' => 'passwor',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    // //会員情報登録--パスワード不一致
    public function test_register_user_validate_confirm_password()
    {
        $data = [
            'name' => 'テストユーザー',
            'email' => "test@example.com",
            'password' => 'password',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    // 会員情報登録--パスワードバリデーション
    public function test_register_user_validate_password()
    {
        $data = [
            'name' => 'テストユーザー',
            'email' => "test@example.com",
            'password' => '',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
}

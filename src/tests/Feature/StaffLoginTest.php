<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Staff;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = Staff::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);
    }

    // ログイン機能成功
    public function test_login_staff()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('attendance.today'));
        $this->assertAuthenticatedAs($this->staff);
    }

    //ログイン--メアドバリデーション
    public function test_login_user_validate_email()
    {
        $response = $this->post('/login', [
            'email' => "",
            'password' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ログイン--パスワードバリデーション
    public function test_login_user_validate_password()
    {
        $response = $this->post('/login', [
            'email' => "test@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン--不一致
    public function test_login_user_validate_user()
    {
        $response = $this->post('/login', [
            'email' => "test2@example.com",
            'password' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}

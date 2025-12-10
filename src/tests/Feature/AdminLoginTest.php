<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Staff;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = Staff::factory()->create([
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
    }

    // ログイン機能成功
    public function test_login_staff()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($this->staff, 'admin');
    }

    //ログイン--メアドバリデーション
    public function test_login_user_validate_email()
    {
        $response = $this->post('/admin/login', [
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
        $response = $this->post('/admin/login', [
            'email' => "admin@example.com",
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
        $response = $this->post('/admin/login', [
            'email' => "admin2@example.com",
            'password' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}

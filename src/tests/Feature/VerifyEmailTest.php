<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use App\Models\Staff;
use Illuminate\Support\Facades\URL;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録後に認証メールが送信される
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $staff = Staff::where('email', 'taro@example.com')->firstOrFail();

        Notification::assertSentTo($staff, VerifyEmailNotification::class);
    }

    // メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function test_staff_can_verify_email_via_verification_link()
    {
        $staff = Staff::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinute(60),
            [
                'id' => $staff->id,
                'hash' => sha1($staff->email),
            ]
            );

            $response = $this->actingAs($staff)->get($verificationUrl);

            $response->assertRedirect(route('attendance.today'));
            $this->assertNotNull($staff->fresh()->email_verified_at);
    }
}

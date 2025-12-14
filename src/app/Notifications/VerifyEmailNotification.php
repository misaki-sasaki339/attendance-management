<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    // 認証リンクの生成
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    //メール内容の定義
    public function toMail($notifiable)
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('【仮登録完了】メールアドレスの確認をお願いします')
            ->greeting($notifiable->name.' 様')
            ->line('このたびはご登録ありがとうございます。')
            ->line('以下のボタンをクリックして、本登録を完了してください。')
            ->action('メールアドレスを確認する', $verifyUrl)
            ->line('このリンクの有効期限は60分です。')
            ->line('このメールにお心当たりがない場合は、破棄してください。')
            ->salutation('COACHTECH運営');
    }
}

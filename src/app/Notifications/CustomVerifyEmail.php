<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    private function greetingByTime()
    {
        $hour = now()->hour;

        if ($hour < 4) {
            return 'こんばんは！';
        } elseif ($hour < 12) {
            return 'おはようございます！';
        } elseif ($hour < 18) {
            return 'こんにちは！';
        } else {
            return 'こんばんは！';
        }
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting($this->greetingByTime())
            ->subject('メールアドレス確認のお願い')
            ->line('ご登録ありがとうございます。')
            ->line('以下のボタンをクリックして、メールアドレスの認証を完了してください。')
            ->action('メールアドレスを認証する', $this->verificationUrl($notifiable))
            ->line('もしこのメールに心当たりがない場合は、このメールを破棄してください。')
            ->salutation("よろしくお願いいたします。");
    }
}

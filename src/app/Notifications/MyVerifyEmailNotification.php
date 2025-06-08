<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;


class MyVerifyEmailNotification extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        // Fortify の認証URL生成ロジックを使用
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // 認証URLを生成
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('アカウントを認証してください！') // 件名
            ->greeting('こんにちは、' . $notifiable->name . '様！') // 挨拶
            ->line('アカウントを認証するために、以下のボタンをクリックしてください。') // 本文
            ->action('アカウント認証', $verificationUrl) // 認証ボタン
            ->line('このメールに心当たりがない場合は、このメールを破棄してください。'); // 結び
    }
}
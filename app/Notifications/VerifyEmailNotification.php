<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('auth.verify_email_title'))
            ->markdown('mail.verify-email', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
                'actionText' => __('auth.verify_email_button'),
            ]);
    }
}

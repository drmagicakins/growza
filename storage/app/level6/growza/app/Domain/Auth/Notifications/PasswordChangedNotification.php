<?php

namespace App\Domain\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent whenever a password changes, via either the settings screen or a
 * reset link.
 *
 * Mail-only at LEVEL 3. The `database` channel and per-user notification
 * preferences belong to LEVEL 12 — but note that security notifications
 * are explicitly NOT user-suppressible even once preferences exist, so
 * this class will keep the mail channel unconditionally.
 */
class PasswordChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly bool $viaReset = false)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $method = $this->viaReset
            ? 'using a password reset link'
            : 'from your account settings';

        return (new MailMessage)
            ->subject('Your Growza password was changed')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('The password on your Growza account was just changed '.$method.'.')
            ->line('If this was you, no action is needed.')
            ->line('If this was not you, your account may be compromised. Reset your password immediately and contact us.')
            ->action('Reset your password', url('/forgot-password'))
            ->line('This message was sent automatically because it concerns the security of your account. It cannot be turned off in your notification settings.');
    }
}

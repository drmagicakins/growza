<?php

namespace App\Domain\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an account is accessed from an IP/user-agent combination that
 * has not been seen recently.
 *
 * Deliberately vague about location: we do not run IP geolocation, and
 * stating a city we have not actually resolved would be inventing data.
 */
class NewDeviceLoginNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $ipAddress,
        private readonly string $userAgent,
        private readonly string $occurredAt,
    ) {
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
        return (new MailMessage)
            ->subject('New sign-in to your Growza account')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your Growza account was signed into from a device or location we have not seen before.')
            ->line('Time: '.$this->occurredAt)
            ->line('IP address: '.$this->ipAddress)
            ->line('Browser: '.$this->userAgent)
            ->line('If this was you, you can ignore this message.')
            ->line('If it was not, reset your password now and enable two-factor authentication.')
            ->action('Secure your account', url('/forgot-password'))
            ->line('This message was sent automatically because it concerns the security of your account.');
    }
}

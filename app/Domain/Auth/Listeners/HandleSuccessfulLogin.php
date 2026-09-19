<?php

namespace App\Domain\Auth\Listeners;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Auth\Notifications\NewDeviceLoginNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Audits every successful login and alerts the account owner when the
 * sign-in comes from a device fingerprint we have not seen recently.
 *
 * "Device" here is a hash of IP + user agent held in the cache for 30
 * days. That is a deliberately modest heuristic: it catches the common
 * case (someone else signing in from elsewhere) without pretending to
 * device-level identification we cannot actually perform.
 */
class HandleSuccessfulLogin
{
    private const REMEMBER_DEVICE_DAYS = 30;

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly Request $request,
    ) {
    }

    public function handle(Login $event): void
    {
        $user = $event->user;

        $this->auditLogger->log(
            action: 'auth.login',
            subject: $user,
            actor: $user,
        );

        $fingerprint = hash('sha256', implode('|', [
            $this->request->ip(),
            (string) $this->request->userAgent(),
        ]));

        $cacheKey = "known-device:{$user->getKey()}:{$fingerprint}";

        if (Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, now()->addDays(self::REMEMBER_DEVICE_DAYS));

            return;
        }

        Cache::put($cacheKey, true, now()->addDays(self::REMEMBER_DEVICE_DAYS));

        // Skip the alert on the very first login of a brand-new account:
        // the user just registered, so telling them their own registration
        // looks suspicious is noise, not security.
        if ($user->created_at !== null && $user->created_at->diffInMinutes(now()) < 5) {
            return;
        }

        $user->notify(new NewDeviceLoginNotification(
            ipAddress: (string) $this->request->ip(),
            userAgent: (string) $this->request->userAgent(),
            occurredAt: now()->toDayDateTimeString().' UTC',
        ));
    }
}

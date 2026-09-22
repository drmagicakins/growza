<?php

namespace App\Domain\Auth\Listeners;

use App\Domain\Audit\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;

/**
 * Records failed authentication attempts so repeated failures against one
 * account are visible in the audit trail (LEVEL 18 / LEVEL 35).
 *
 * The attempted password is never touched — only the identifier used.
 */
class AuditFailedLogin
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function handle(Failed $event): void
    {
        $this->auditLogger->log(
            action: 'auth.login_failed',
            subject: $event->user,
            actor: $event->user,
            after: ['email' => $event->credentials['email'] ?? null],
            actorLabel: $event->user === null ? 'unknown-account' : null,
        );
    }
}

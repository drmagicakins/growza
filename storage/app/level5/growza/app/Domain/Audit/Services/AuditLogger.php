<?php

namespace App\Domain\Audit\Services;

use App\Domain\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Single entry point for writing audit records.
 *
 * Everything that touches authentication, authorization, money or
 * configuration routes through here rather than calling AuditLog::create()
 * directly, so request metadata is captured consistently and secret
 * scrubbing happens in exactly one place.
 */
class AuditLogger
{
    /**
     * Keys whose values must never reach the audit table, even if a caller
     * passes them in a before/after snapshot. Matched case-insensitively
     * against a substring of the key.
     */
    private const REDACTED_KEYS = [
        'password',
        'token',
        'secret',
        'api_key',
        'apikey',
        'authorization',
        'two_factor',
        'recovery_code',
        'card',
        'cvv',
        'pin',
    ];

    public function __construct(private readonly Request $request)
    {
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function log(
        string $action,
        ?Model $subject = null,
        ?Model $actor = null,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
        ?string $actorLabel = null,
    ): AuditLog {
        $actor ??= $this->request->user();

        return AuditLog::create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            // A null actor means a system/queue-triggered action; the label
            // records what that was so the row is still interpretable.
            'actor_label' => $actorLabel ?? ($actor === null ? 'system' : null),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'before' => $before === null ? null : $this->redact($before),
            'after' => $after === null ? null : $this->redact($after),
            'reason' => $reason,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->truncate((string) $this->request->userAgent()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redact($value);

                continue;
            }

            foreach (self::REDACTED_KEYS as $needle) {
                if (str_contains(strtolower((string) $key), $needle)) {
                    $data[$key] = '[redacted]';

                    break;
                }
            }
        }

        return $data;
    }

    private function truncate(string $value, int $limit = 512): ?string
    {
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }
}

<?php

namespace App\Domain\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An immutable-style record of a sensitive operation (LEVEL 18 schema,
 * first written to at LEVEL 3).
 *
 * Audit rows are written once and never updated or deleted by application
 * code — `$timestamps` is disabled because the table carries only
 * `created_at`, and there is deliberately no `updated_at` to change.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'actor_label',
        'action',
        'subject_type',
        'subject_id',
        'before',
        'after',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}

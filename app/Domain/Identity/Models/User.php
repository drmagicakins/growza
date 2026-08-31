<?php

namespace App\Domain\Identity\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * The Growza User model lives in the Identity domain (see ARCHITECTURE.md
 * §3), not in the framework-default App\Models namespace, so domain code
 * never reaches "up and out" of app/Domain for its own primary actor.
 *
 * Business logic (suspension rules, wallet access, referral code issuance)
 * belongs in Domain\Identity\Services / other domains' services — this
 * model intentionally stays a thin Eloquent representation plus
 * relationships, per the "Models represent domain data" rule (§2).
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || $this->status === 'banned';
    }

    // Relationships to Wallet, Orders, ReferralCode, SupportTickets etc.
    // are added as each owning domain is implemented (Batch B onward per
    // ARCHITECTURE.md §21) — intentionally absent at LEVEL 0 so this file
    // isn't touched repeatedly as a merge-conflict magnet across levels.
}

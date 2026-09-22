<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class)],

            // Phone is required at registration (LEVEL 3 field list) and
            // unique, since it doubles as a recovery/contact channel.
            'phone' => ['required', 'string', 'min:7', 'max:32', Rule::unique(User::class)],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(10)->letters()->numbers()->uncompromised(),
            ],

            // Optional. Recorded as a plain string — LEVEL 14 resolves it
            // into a referral conversion. Not validated against existing
            // codes, because the referral_codes table does not exist yet
            // and silently rejecting a valid-looking code would be worse
            // than recording it for later reconciliation.
            'referral_code' => ['nullable', 'string', 'max:32'],

            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'You must accept the Terms of Service and Privacy Policy to register.',
            'password.uncompromised' => 'That password has appeared in a known data breach. Please choose a different one.',
        ])->validate();

        // Wrapped in a transaction so that when LEVEL 8 adds wallet creation
        // and LEVEL 14 adds referral resolution on registration, a failure in
        // either cannot leave a half-created account behind.
        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => $input['password'],
            ]);

            if (! empty($input['referral_code'])) {
                $user->referred_by_code = trim($input['referral_code']);
                $user->save();
            }

            // Every public registration gets the Customer role. Anything
            // above it (Support Agent, Administrator, ...) is granted by an
            // existing administrator through the admin panel (LEVEL 16),
            // never reachable through this self-registration form.
            $user->assignRole(RoleName::default()->value);

            $this->auditLogger->log(
                action: 'user.registered',
                subject: $user,
                actor: $user,
                after: ['email' => $user->email, 'referred_by_code' => $user->referred_by_code],
            );

            return $user;
        });
    }
}

<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Auth\Notifications\PasswordChangedNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * @param  array<string, string>  $input
     */
    public function update(Authenticatable $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(10)->letters()->numbers()->uncompromised(),
            ],
        ], [
            'current_password.current_password' => 'The password you entered does not match your current password.',
            'password.uncompromised' => 'That password has appeared in a known data breach. Please choose a different one.',
        ])->validateWithBag('updatePassword');

        $user->forceFill(['password' => $input['password']])->save();

        // Never log the password itself — the audit record captures that a
        // change happened, not what it changed to. See AuditLogger::redact().
        $this->auditLogger->log(
            action: 'user.password_changed',
            subject: $user,
            actor: $user,
        );

        // A password change the account owner did not initiate is the
        // clearest early signal of a compromised account, so this
        // notification is not user-suppressible (see LEVEL 12).
        $user->notify(new PasswordChangedNotification);
    }
}

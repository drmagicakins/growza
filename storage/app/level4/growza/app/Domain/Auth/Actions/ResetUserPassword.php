<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Auth\Notifications\PasswordChangedNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * @param  array<string, string>  $input
     */
    public function reset(Authenticatable $user, array $input): void
    {
        Validator::make($input, [
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(10)->letters()->numbers()->uncompromised(),
            ],
        ], [
            'password.uncompromised' => 'That password has appeared in a known data breach. Please choose a different one.',
        ])->validate();

        $user->forceFill(['password' => $input['password']])->save();

        $this->auditLogger->log(
            action: 'user.password_reset',
            subject: $user,
            actor: $user,
        );

        $user->notify(new PasswordChangedNotification(viaReset: true));
    }
}

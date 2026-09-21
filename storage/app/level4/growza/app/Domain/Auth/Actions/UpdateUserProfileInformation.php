<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
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
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['required', 'string', 'min:7', 'max:32', Rule::unique(User::class)->ignore($user->id)],
        ])->validateWithBag('updateProfileInformation');

        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        // Changing the email address invalidates verification: the new
        // address is unproven until the user clicks the new link.
        if ($input['email'] !== $user->email) {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'email_verified_at' => null,
            ])->save();

            $user->sendEmailVerificationNotification();
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'phone' => $input['phone'],
            ])->save();
        }

        $this->auditLogger->log(
            action: 'user.profile_updated',
            subject: $user,
            actor: $user,
            before: $before,
            after: ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone],
        );
    }
}

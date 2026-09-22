<?php

use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

beforeEach(fn () => (new CatalogueSeeder)->run());

it('shows real services on the dashboard services page, marked as not yet orderable', function () {
    $user = User::forceCreate([
        'name' => 'Test Customer',
        'email' => 'catalogue-dash@example.com',
        'phone' => '+2348033332222',
        'password' => Hash::make('correct-horse-99'),
        'email_verified_at' => now(),
        'status' => 'active',
    ]);
    $user->assignRole(RoleName::Customer->value);

    actingAs($user)->get(route('dashboard.services'))
        ->assertOk()
        // No `escape: false`: the service name contains a literal "&", which
        // Blade renders as "&amp;". See the same note in CatalogueTest.
        ->assertSee('Instagram & Facebook Ad Campaign')
        ->assertSee('Ordering opens soon', escape: false);
});

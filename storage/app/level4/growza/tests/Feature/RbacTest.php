<?php

use App\Domain\Identity\Enums\PermissionName;
use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function rbacUser(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'name' => 'Test User',
        'email' => 'rbac-'.uniqid().'@example.com',
        'phone' => '+234'.random_int(7000000000, 7999999999),
        'password' => Hash::make('correct-horse-99'),
        'email_verified_at' => now(),
        'status' => 'active',
    ], $overrides));
}

it('seeds exactly the nine platform roles', function () {
    $names = Role::pluck('name')->sort()->values()->all();

    expect($names)->toBe(collect(RoleName::cases())->map->value->sort()->values()->all());
});

it('seeds exactly the eleven platform permissions', function () {
    $names = Permission::pluck('name')->sort()->values()->all();

    expect($names)->toBe(collect(PermissionName::values())->sort()->values()->all());
});

it('grants an Administrator broad access but withholds manage_settings', function () {
    $admin = rbacUser();
    $admin->assignRole(RoleName::Administrator->value);

    expect($admin->can('manage_orders'))->toBeTrue()
        ->and($admin->can('view_payments'))->toBeTrue()
        ->and($admin->can('manage_settings'))->toBeFalse();
});

it('grants Support Agent read access without financial permissions', function () {
    $agent = rbacUser();
    $agent->assignRole(RoleName::SupportAgent->value);

    expect($agent->can('view_orders'))->toBeTrue()
        ->and($agent->can('view_users'))->toBeTrue()
        ->and($agent->can('manage_refunds'))->toBeFalse()
        ->and($agent->can('manage_settings'))->toBeFalse();
});

it('grants a Customer no admin-facing permissions at all', function () {
    $customer = rbacUser();
    $customer->assignRole(RoleName::Customer->value);

    foreach (PermissionName::values() as $permission) {
        expect($customer->can($permission))->toBeFalse();
    }
});

it('lets a Super Admin bypass every permission without an explicit grant', function () {
    $superAdmin = rbacUser();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);

    // No syncPermissions() call exists for Super Admin in the seeder —
    // this proves the Gate::before bypass, not a forgotten grant.
    foreach (PermissionName::values() as $permission) {
        expect($superAdmin->can($permission))->toBeTrue();
    }
});

it('assigns the Customer role automatically at registration', function () {
    post(route('register'), [
        'name' => 'New Signup',
        'email' => 'newsignup@example.com',
        'phone' => '+2348099998877',
        'password' => 'correct-horse-99',
        'password_confirmation' => 'correct-horse-99',
        'terms' => '1',
    ]);

    $user = User::where('email', 'newsignup@example.com')->first();

    expect($user->hasRole(RoleName::Customer->value))->toBeTrue()
        ->and($user->hasRole(RoleName::Administrator->value))->toBeFalse();
});

it('blocks a customer from the permission-gated admin placeholder', function () {
    $customer = rbacUser();
    $customer->assignRole(RoleName::Customer->value);

    actingAs($customer)->get('/admin')->assertForbidden();
});

it('admits a user whose role grants view_users to the admin placeholder', function () {
    $admin = rbacUser();
    $admin->assignRole(RoleName::Administrator->value);

    actingAs($admin)->get('/admin')->assertOk();
});

it('admits a Super Admin to the admin placeholder via bypass alone', function () {
    $superAdmin = rbacUser();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);

    actingAs($superAdmin)->get('/admin')->assertOk();
});

it('keeps a guest out of the admin placeholder', function () {
    get('/admin')->assertRedirect(route('login'));
});

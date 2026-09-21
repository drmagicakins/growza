<?php

use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    $this->policy = new UserPolicy;
});

function policyUser(string $role): User
{
    $user = User::forceCreate([
        'name' => 'Policy Test',
        'email' => 'policy-'.uniqid().'@example.com',
        'phone' => '+234'.random_int(7000000000, 7999999999),
        'password' => bcrypt('correct-horse-99'),
        'status' => 'active',
    ]);
    $user->assignRole($role);

    return $user;
}

it('never allows a user to suspend themselves', function () {
    $admin = policyUser(RoleName::Administrator->value);

    expect($this->policy->suspend($admin, $admin))->toBeFalse();
});

it('does not let an Administrator suspend a Super Admin', function () {
    $admin = policyUser(RoleName::Administrator->value);
    $superAdmin = policyUser(RoleName::SuperAdmin->value);

    expect($this->policy->suspend($admin, $superAdmin))->toBeFalse();
});

it('lets a Super Admin suspend another Super Admin', function () {
    $superAdmin1 = policyUser(RoleName::SuperAdmin->value);
    $superAdmin2 = policyUser(RoleName::SuperAdmin->value);

    expect($this->policy->suspend($superAdmin1, $superAdmin2))->toBeTrue();
});

it('lets an Administrator suspend an ordinary customer', function () {
    $admin = policyUser(RoleName::Administrator->value);
    $customer = policyUser(RoleName::Customer->value);

    expect($this->policy->suspend($admin, $customer))->toBeTrue();
});

it('does not let a customer suspend anyone', function () {
    $customer = policyUser(RoleName::Customer->value);
    $otherCustomer = policyUser(RoleName::Customer->value);

    expect($this->policy->suspend($customer, $otherCustomer))->toBeFalse();
});

it('always lets a user view their own account', function () {
    $customer = policyUser(RoleName::Customer->value);

    expect($this->policy->view($customer, $customer))->toBeTrue();
});

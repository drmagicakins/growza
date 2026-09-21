<?php

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Every Feature/Integration test gets a freshly migrated database AND the
 * platform's roles/permissions seeded (LEVEL 4). Without this, any test
 * that registers a user — which assigns the Customer role — throws
 * RoleDoesNotExist. Seeding here, once, means individual test files never
 * need to remember to call $this->seed() themselves.
 */
uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed(RoleAndPermissionSeeder::class))
    ->in('Feature', 'Integration');

uses(TestCase::class)->in('Unit');

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CatalogueSeeder::class,
        ]);

        // Demo users/orders/etc are added by whichever level introduces
        // realistic factories for them (ARCHITECTURE.md §37 — demo data
        // is a development/staging concern, never seeded with production
        // secrets and never assumed to exist by application code).
    }
}

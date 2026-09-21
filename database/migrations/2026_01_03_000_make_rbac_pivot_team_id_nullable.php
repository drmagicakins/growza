<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allows `team_id` to be NULL on the two RBAC pivot tables (LEVEL 4 fix).
 *
 * THE BUG THIS FIXES
 * ------------------
 * The LEVEL 0 migration copied spatie/laravel-permission's published stub
 * verbatim, and that stub — with `'teams' => true` — does two things that
 * cannot coexist with the "global grant" model this codebase uses until
 * Agencies (LEVEL 21) arrive:
 *
 *   1. It creates `model_has_roles.team_id` / `model_has_permissions.team_id`
 *      as NOT NULL.
 *   2. It makes `team_id` the *first column of the composite PRIMARY KEY*.
 *
 * `DefaultTeamResolver` initialises `teamId` to `null` and nothing here
 * calls `setPermissionsTeamId()` yet, so `HasRoles::assignRole()` builds its
 * pivot as `['team_id' => getPermissionsTeamId()]` — i.e. `['team_id' => null]`
 * — and issues `INSERT ... team_id = NULL`. The NOT NULL constraint rejects
 * it, so EVERY role assignment threw
 * `SQLSTATE[23000] ... NOT NULL constraint failed: model_has_roles.team_id`,
 * taking out all 17 RBAC-related tests (11 RBAC feature + 6 UserPolicy) plus
 * registration itself, which assigns Customer.
 *
 * WHY A COLUMN `->change()` IS NOT ENOUGH — THE REASON THIS MIGRATION LOOKS
 * HEAVIER THAN IT SHOULD
 * ------------------------------------------------------------------------
 * The obvious fix — `$table->unsignedBigInteger('team_id')->nullable()->change()`
 * — is a silent no-op on MySQL/InnoDB. **Every column of a PRIMARY KEY is
 * implicitly NOT NULL in MySQL**, and the engine will not let it become
 * nullable: the `ALTER TABLE ... MODIFY team_id ... NULL` is accepted, the
 * migration reports DONE, and `information_schema.COLUMNS.IS_NULLABLE` still
 * reads `NO`. (Verified on this machine's MySQL 8 — see the note below.)
 * So the key itself has to change, not just the column: the PRIMARY KEY is
 * dropped and replaced by an equivalent UNIQUE index. A UNIQUE index, unlike
 * a PRIMARY KEY, *does* permit NULLs — on both MySQL and SQLite — while still
 * guaranteeing exactly what the PK guaranteed: no duplicate grant rows.
 *
 * SQLite in-memory (what the Pest suite runs on, per phpunit.xml) tolerates
 * NULLs inside a composite PK, so the tests were green even while the schema
 * the app actually runs against was broken — a false green worth remembering
 * for any future schema change. This migration therefore changes the key shape
 * so it is correct on both drivers, and both paths were exercised (MySQL 8 via
 * `migrate`/`db:seed`/a real `assignRole()`, SQLite via the Pest suite).
 *
 * WHY NULLABLE IS THE CORRECT SHAPE, NOT A WORKAROUND
 * ---------------------------------------------------
 * A NULL team_id is the documented meaning of "this grant is global, not
 * scoped to a team". Spatie's own queries read it that way — `roles()` and
 * `permissions()` filter on `wherePivot($teamsKey, getPermissionsTeamId())`,
 * which matches NULL-to-NULL. LEVEL 21 sets a real team id at request time
 * and rows written then carry that id; the rows written now (global roles
 * like Super Admin / Customer) continue to mean "global". Nothing needs to
 * be backfilled.
 *
 * Additive rather than an edit to the LEVEL 0 migration, per DATABASE.md:
 * "a later level that needs to change a LEVEL 0 table's shape will get a
 * new migration, not an edit to these files". That also keeps the fix
 * applicable to any environment where LEVEL 0 already ran.
 */
return new class extends Migration
{
    /**
     * The pivot tables and the owning FK column/table each one references.
     * The FK has to be dropped and re-added because MySQL will not modify a
     * column that is under a foreign key while the primary key is rewritten.
     *
     * @var array<string, array{foreign: string, references: string}>
     */
    private const PIVOTS = [
        'model_has_roles' => ['foreign' => 'role_id', 'references' => 'roles'],
        'model_has_permissions' => ['foreign' => 'permission_id', 'references' => 'permissions'],
    ];

    public function up(): void
    {
        $teamKey = config('permission.column_names.team_foreign_key', 'team_id');

        foreach (self::PIVOTS as $pivot => $meta) {
            if (! Schema::hasColumn($pivot, $teamKey)) {
                continue;
            }

            $this->dropForeignIfExists($pivot, $meta['foreign']);

            // Drop the composite primary key (which forces team_id NOT NULL),
            // then re-express the same uniqueness as a UNIQUE index — which,
            // unlike a PRIMARY KEY, permits NULLs — and make the column
            // nullable. Done in separate statements because MySQL will not
            // rewrite a primary key and modify a column of it in one ALTER.
            Schema::table($pivot, function (Blueprint $table) {
                $table->dropPrimary();
            });

            Schema::table($pivot, function (Blueprint $table) use ($teamKey, $meta) {
                $table->unsignedBigInteger($teamKey)->nullable()->change();
                $table->unique(
                    [$teamKey, $meta['foreign'], 'model_id', 'model_type'],
                    $meta['foreign'].'_team_unique'
                );
            });

            $this->restoreForeign($pivot, $meta);
        }
    }

    public function down(): void
    {
        // Reverting restores NOT NULL + the composite PRIMARY KEY, which can
        // only succeed once no global (NULL) grants exist — i.e. after LEVEL 21
        // is the norm. Any surviving NULL row would abort the rollback, so the
        // assumption is made explicit rather than left to fail midway.
        $teamKey = config('permission.column_names.team_foreign_key', 'team_id');

        foreach (self::PIVOTS as $pivot => $meta) {
            if (! Schema::hasColumn($pivot, $teamKey)) {
                continue;
            }

            $this->dropForeignIfExists($pivot, $meta['foreign']);

            $this->dropUniqueIfExists($pivot, $meta['foreign'].'_team_unique');

            Schema::table($pivot, function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable(false)->change();
            });

            Schema::table($pivot, function (Blueprint $table) use ($teamKey, $meta) {
                $table->primary([$teamKey, $meta['foreign'], 'model_id', 'model_type']);
            });

            $this->restoreForeign($pivot, $meta);
        }
    }

    /**
     * Drop a pivot's foreign key by convention name, ignoring the case where
     * it is already absent (re-running `up` on a partially-migrated DB).
     */
    private function dropForeignIfExists(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
            });
        } catch (Throwable) {
            // No FK under that name on this driver/DB — nothing to drop.
        }
    }

    /**
     * Drop a unique index by name, ignoring the case where it is absent.
     *
     * Guards the rollback of environments where the earlier, broken revision
     * of this migration ran: it never created the index, so `down()` must not
     * assume it exists.
     */
    private function dropUniqueIfExists(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropUnique($index);
            });
        } catch (Throwable) {
            // Index not present on this DB — nothing to drop.
        }
    }

    /**
     * Re-create the pivot's cascading delete FK to its owning table.
     *
     * @param  array{foreign: string, references: string}  $meta
     */
    private function restoreForeign(string $pivot, array $meta): void
    {
        Schema::table($pivot, function (Blueprint $table) use ($meta) {
            $table->foreign($meta['foreign'])
                ->references('id')->on($meta['references'])
                ->onDelete('cascade');
        });
    }
};

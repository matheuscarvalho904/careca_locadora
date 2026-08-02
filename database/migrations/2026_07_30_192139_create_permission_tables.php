<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

        throw_if(
            empty($tableNames),
            'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.'
        );

        throw_if(
            $teams && empty($teamForeignKey),
            'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.'
        );

        Schema::create($tableNames['permissions'], static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestampsTz();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create(
            $tableNames['roles'],
            static function (Blueprint $table) use ($teams, $teamForeignKey): void {
                $table->id();

                if ($teams || config('permission.testing')) {
                    /*
                     * UUID para permitir que o escopo de equipes seja ligado
                     * futuramente à organização do SaaS.
                     */
                    $table->uuid($teamForeignKey)->nullable();
                    $table->index($teamForeignKey, 'roles_team_foreign_key_index');
                }

                $table->string('name');
                $table->string('guard_name');
                $table->timestampsTz();

                if ($teams || config('permission.testing')) {
                    $table->unique([$teamForeignKey, 'name', 'guard_name']);
                } else {
                    $table->unique(['name', 'guard_name']);
                }
            }
        );

        Schema::create(
            $tableNames['model_has_permissions'],
            static function (Blueprint $table) use (
                $tableNames,
                $pivotPermission,
                $modelMorphKey,
                $teams,
                $teamForeignKey
            ): void {
                $table->unsignedBigInteger($pivotPermission);

                $table->string('model_type');
                $table->uuid($modelMorphKey);

                $table->index(
                    [$modelMorphKey, 'model_type'],
                    'model_has_permissions_model_id_model_type_index'
                );

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();

                if ($teams) {
                    $table->uuid($teamForeignKey);

                    $table->index(
                        $teamForeignKey,
                        'model_has_permissions_team_foreign_key_index'
                    );

                    $table->primary(
                        [
                            $teamForeignKey,
                            $pivotPermission,
                            $modelMorphKey,
                            'model_type',
                        ],
                        'model_has_permissions_permission_model_type_primary'
                    );
                } else {
                    $table->primary(
                        [
                            $pivotPermission,
                            $modelMorphKey,
                            'model_type',
                        ],
                        'model_has_permissions_permission_model_type_primary'
                    );
                }
            }
        );

        Schema::create(
            $tableNames['model_has_roles'],
            static function (Blueprint $table) use (
                $tableNames,
                $pivotRole,
                $modelMorphKey,
                $teams,
                $teamForeignKey
            ): void {
                $table->unsignedBigInteger($pivotRole);

                $table->string('model_type');
                $table->uuid($modelMorphKey);

                $table->index(
                    [$modelMorphKey, 'model_type'],
                    'model_has_roles_model_id_model_type_index'
                );

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();

                if ($teams) {
                    $table->uuid($teamForeignKey);

                    $table->index(
                        $teamForeignKey,
                        'model_has_roles_team_foreign_key_index'
                    );

                    $table->primary(
                        [
                            $teamForeignKey,
                            $pivotRole,
                            $modelMorphKey,
                            'model_type',
                        ],
                        'model_has_roles_role_model_type_primary'
                    );
                } else {
                    $table->primary(
                        [
                            $pivotRole,
                            $modelMorphKey,
                            'model_type',
                        ],
                        'model_has_roles_role_model_type_primary'
                    );
                }
            }
        );

        Schema::create(
            $tableNames['role_has_permissions'],
            static function (Blueprint $table) use (
                $tableNames,
                $pivotRole,
                $pivotPermission
            ): void {
                $table->unsignedBigInteger($pivotPermission);
                $table->unsignedBigInteger($pivotRole);

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();

                $table->primary(
                    [$pivotPermission, $pivotRole],
                    'role_has_permissions_permission_id_role_id_primary'
                );
            }
        );

        app('cache')
            ->store(
                config('permission.cache.store') !== 'default'
                    ? config('permission.cache.store')
                    : null
            )
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(
            empty($tableNames),
            'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.'
        );

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
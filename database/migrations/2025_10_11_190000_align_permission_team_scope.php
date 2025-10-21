<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureCompanyColumn('model_has_roles', 'role_id');
        $this->ensureCompanyColumn('model_has_permissions', 'permission_id');

        $this->refreshPrimary('model_has_roles', 'model_has_roles_role_model_type_primary', [
            'company_id', 'role_id', 'model_id', 'model_type',
        ]);

        $this->refreshPrimary('model_has_permissions', 'model_has_permissions_permission_model_type_primary', [
            'company_id', 'permission_id', 'model_id', 'model_type',
        ]);
    }

    public function down(): void
    {
        $this->refreshPrimary('model_has_roles', 'model_has_roles_role_model_type_primary', [
            'role_id', 'model_id', 'model_type',
        ]);

        $this->refreshPrimary('model_has_permissions', 'model_has_permissions_permission_model_type_primary', [
            'permission_id', 'model_id', 'model_type',
        ]);

        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            if (Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, static function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }
    }

    protected function ensureCompanyColumn(string $table, string $after): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'company_id')) {
            return;
        }

        Schema::table($table, static function (Blueprint $table) use ($after) {
            $table->unsignedBigInteger('company_id')->after($after);
        });
    }

    protected function refreshPrimary(string $table, string $name, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, static function (Blueprint $table) use ($name) {
                $table->dropPrimary($name);
            });
        } catch (\Throwable $exception) {
            // The index might not exist; ignore and recreate below.
        }

        Schema::table($table, static function (Blueprint $table) use ($name, $columns) {
            $table->primary($columns, $name);
        });
    }
};

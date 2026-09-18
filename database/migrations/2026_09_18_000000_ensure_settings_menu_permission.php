<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'settings.menu', 'guard_name' => 'web'],
            ['group_name' => 'settings', 'updated_at' => $now, 'created_at' => $now]
        );

        $permissionId = DB::table('permissions')
            ->where('name', 'settings.menu')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('roles')
            ->whereIn('name', ['SuperAdmin', 'Manager'])
            ->where('guard_name', 'web')
            ->pluck('id')
            ->each(function ($roleId) use ($permissionId): void {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Additive permission migration: keep permission on rollback to avoid hiding menus.
    }
};

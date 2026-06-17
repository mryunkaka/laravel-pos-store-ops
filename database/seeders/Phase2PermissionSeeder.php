<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Phase2PermissionSeeder extends Seeder
{
    /**
     * Seed permissions and role assignments for Phase 2 features.
     */
    public function run(): void
    {
        // Create new permissions if not exists
        $permissions = [
            ['name' => 'void.order', 'group_name' => 'orders'],
            ['name' => 'allow-negative-stock', 'group_name' => 'stock'],
            ['name' => 'audit.menu', 'group_name' => 'audit'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['group_name' => $perm['group_name']]
            );
        }

        // Grant void.order and audit.menu to SuperAdmin
        $superAdmin = Role::where('name', 'SuperAdmin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(Permission::all());
        }

        // Grant void.order to Manager
        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $manager->givePermissionTo(['void.order']);
        }
    }
}

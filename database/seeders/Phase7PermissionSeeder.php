<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Phase7PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'report.menu', 'group_name' => 'report'],
            ['name' => 'settings.menu', 'group_name' => 'settings'],
            ['name' => 'restore-database', 'group_name' => 'database'],
            ['name' => 'discount.order', 'group_name' => 'pos'],
            ['name' => 'edit-price.order', 'group_name' => 'pos'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['group_name' => $permission['group_name']]
            );
        }

        Role::where('name', 'SuperAdmin')->first()?->givePermissionTo(Permission::all());
        Role::where('name', 'Manager')->first()?->givePermissionTo([
            'report.menu',
            'audit.menu',
            'settings.menu',
            'discount.order',
            'edit-price.order',
        ]);
    }
}

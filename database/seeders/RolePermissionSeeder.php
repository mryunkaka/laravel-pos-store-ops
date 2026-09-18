<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'pos.menu', 'group_name' => 'pos'],
            ['name' => 'employee.menu', 'group_name' => 'employee'],
            ['name' => 'customer.menu', 'group_name' => 'customer'],
            ['name' => 'supplier.menu', 'group_name' => 'supplier'],
            ['name' => 'salary.menu', 'group_name' => 'salary'],
            ['name' => 'attendance.menu', 'group_name' => 'attendance'],
            ['name' => 'category.menu', 'group_name' => 'category'],
            ['name' => 'product.menu', 'group_name' => 'product'],
            ['name' => 'orders.menu', 'group_name' => 'orders'],
            ['name' => 'stock.menu', 'group_name' => 'stock'],
            ['name' => 'roles.menu', 'group_name' => 'roles'],
            ['name' => 'user.menu', 'group_name' => 'user'],
            ['name' => 'database.menu', 'group_name' => 'database'],
            ['name' => 'void.order', 'group_name' => 'orders'],
            ['name' => 'allow-negative-stock', 'group_name' => 'stock'],
            ['name' => 'audit.menu', 'group_name' => 'audit'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['group_name' => $permission['group_name']]
            );
        }

        // Create Roles and Assign Permissions
        Role::firstOrCreate(['name' => 'SuperAdmin'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'Admin'])->syncPermissions(['customer.menu', 'user.menu', 'supplier.menu', 'attendance.menu']);
        Role::firstOrCreate(['name' => 'Account'])->syncPermissions(['customer.menu', 'user.menu', 'supplier.menu']);
        Role::firstOrCreate(['name' => 'Manager'])->syncPermissions(['stock.menu', 'orders.menu', 'product.menu', 'salary.menu', 'employee.menu', 'attendance.menu', 'void.order']);
    }
}

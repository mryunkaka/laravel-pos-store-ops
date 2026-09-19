<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeExperienceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_form_shows_zero_experience_option(): void
    {
        $user = $this->authorizedUser();

        $response = $this->actingAs($user)->get('/employees/create');

        $response->assertOk();
        $response->assertSee('value="0 Pengalaman"', false);
        $response->assertSee('0 Pengalaman', false);
    }

    public function test_employee_can_be_created_with_zero_experience(): void
    {
        $user = $this->authorizedUser();

        $response = $this->actingAs($user)->post('/employees', [
            'name' => 'Karyawan Tanpa Pengalaman',
            'email' => 'employee-zero-experience@example.test',
            'phone' => '081234567891',
            'experience' => '0 Pengalaman',
            'salary' => 5000000,
            'vacation' => 'Minggu',
            'city' => 'Jakarta',
            'address' => 'Alamat karyawan test',
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', [
            'email' => 'employee-zero-experience@example.test',
            'experience' => '0 Pengalaman',
        ]);
    }

    private function authorizedUser(): User
    {
        Permission::firstOrCreate(
            ['name' => 'employee.menu', 'guard_name' => 'web'],
            ['group_name' => 'employee']
        );

        $role = Role::firstOrCreate(['name' => 'employee-form-test', 'guard_name' => 'web']);
        $role->givePermissionTo('employee.menu');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

<?php

namespace Tests\Feature;

use App\Models\AdvanceSalary;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaySalaryControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_advance_salary_form_prefills_month_and_year_from_advance_salary(): void
    {
        $user = $this->authorizedUser();
        $employee = Employee::factory()->create();
        $advanceSalary = AdvanceSalary::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-19',
            'advance_salary' => 100000,
            'is_deducted' => false,
        ]);

        $response = $this->actingAs($user)->get('/pay-salary/' . $advanceSalary->id);

        $response->assertOk();
        $response->assertSee('option value="09" selected', false);
        $response->assertSee('option value="2026" selected', false);
    }

    public function test_advance_salary_form_can_pay_salary_using_its_advance_record_id(): void
    {
        $user = $this->authorizedUser();
        $employee = Employee::factory()->create(['salary' => 1000000]);
        $advanceSalary = AdvanceSalary::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-19',
            'advance_salary' => 100000,
            'is_deducted' => false,
        ]);

        $response = $this->from('/pay-salary/' . $advanceSalary->id)
            ->actingAs($user)
            ->post('/pay-salary', [
                'id' => $advanceSalary->id,
                'month' => '09',
                'year' => '2026',
                'date' => '2026-09-19',
            ]);

        $response->assertRedirect(route('pay-salary.payHistory'));
        $this->assertDatabaseHas('pay_salaries', [
            'employee_id' => $employee->id,
            'salary_month' => '09-2026',
            'paid_amount' => 1000000,
            'advance_salary' => 100000,
            'due_salary' => 900000,
        ]);
        $this->assertDatabaseHas('advance_salaries', [
            'id' => $advanceSalary->id,
            'is_deducted' => 1,
        ]);
    }

    public function test_single_employee_payment_still_works_without_advance_salary(): void
    {
        $user = $this->authorizedUser();
        $employee = Employee::factory()->create(['salary' => 800000]);

        $response = $this->actingAs($user)->post('/pay-salary', [
            'employee_id' => $employee->id,
            'month' => '09',
            'year' => '2026',
            'date' => '2026-09-19',
        ]);

        $response->assertRedirect(route('pay-salary.payHistory'));
        $this->assertDatabaseHas('pay_salaries', [
            'employee_id' => $employee->id,
            'salary_month' => '09-2026',
            'paid_amount' => 800000,
            'advance_salary' => 0,
            'due_salary' => 800000,
        ]);
    }

    public function test_payment_validation_error_is_visible_on_payment_form(): void
    {
        $user = $this->authorizedUser();
        $employee = Employee::factory()->create();
        $advanceSalary = AdvanceSalary::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-19',
            'advance_salary' => 100000,
            'is_deducted' => false,
        ]);

        $response = $this->from('/pay-salary/' . $advanceSalary->id)
            ->actingAs($user)
            ->post('/pay-salary', [
                'id' => $advanceSalary->id,
                'month' => '',
                'year' => '2026',
                'date' => '2026-09-19',
            ]);

        $followed = $this->followRedirects($response);

        $followed->assertSee('Bulan gaji wajib dipilih.', false);
    }

    private function authorizedUser(): User
    {
        Permission::firstOrCreate(
            ['name' => 'salary.menu', 'guard_name' => 'web'],
            ['group_name' => 'salary']
        );

        $role = Role::firstOrCreate(['name' => 'salary-form-test', 'guard_name' => 'web']);
        $role->givePermissionTo('salary.menu');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

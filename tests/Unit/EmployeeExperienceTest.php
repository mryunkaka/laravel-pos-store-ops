<?php

namespace Tests\Unit;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmployeeExperienceTest extends TestCase
{
    public function test_zero_experience_and_existing_options_pass_store_validation(): void
    {
        $rules = (new StoreEmployeeRequest())->rules();

        foreach (['0 Pengalaman', '1 Tahun', '5+ Tahun'] as $experience) {
            $validator = Validator::make([
                'name' => 'Karyawan Test',
                'email' => 'employee-test@example.com',
                'phone' => '081234567890',
                'experience' => $experience,
                'salary' => 5000000,
                'city' => 'Jakarta',
                'address' => 'Alamat test',
            ], $rules);

            $this->assertFalse($validator->fails(), $experience . ' harus valid.');
        }
    }

    public function test_experience_must_match_available_options_on_store_and_update(): void
    {
        foreach ([StoreEmployeeRequest::class, UpdateEmployeeRequest::class] as $requestClass) {
            $rules = (new $requestClass())->rules();
            $validator = Validator::make(['experience' => '10 Tahun'], $rules);

            $this->assertTrue($validator->fails(), $requestClass . ' harus menolak opsi pengalaman di luar daftar.');
        }
    }
}

<?php

namespace App\Http\Requests\PaySalary;

use Illuminate\Foundation\Http\FormRequest;

class StorePaySalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'required_without:employee_id', 'exists:advance_salaries,id'],
            'employee_id' => ['nullable', 'required_without:id', 'exists:employees,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'month' => ['required', 'regex:/^(0[1-9]|1[0-2])$/'],
            'year' => ['required', 'integer', 'digits:4', 'min:2000', 'max:' . (now()->year + 1)],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required_without' => 'Data gaji di muka atau karyawan wajib dipilih.',
            'id.exists' => 'Data gaji di muka tidak ditemukan.',
            'employee_id.required_without' => 'Data karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'date.required' => 'Tanggal pembayaran wajib diisi.',
            'date.date_format' => 'Format tanggal pembayaran tidak valid.',
            'month.required' => 'Bulan gaji wajib dipilih.',
            'month.regex' => 'Bulan gaji tidak valid.',
            'year.required' => 'Tahun gaji wajib dipilih.',
            'year.digits' => 'Tahun gaji harus 4 digit.',
        ];
    }
}

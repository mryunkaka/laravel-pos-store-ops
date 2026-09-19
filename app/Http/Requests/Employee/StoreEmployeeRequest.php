<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => 'image|file|max:1024',
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:employees,email',
            'phone' => 'required|string|max:15|unique:employees,phone',
            'experience' => ['nullable', Rule::in(Employee::EXPERIENCE_OPTIONS)],
            'salary' => 'required|numeric',
            'vacation' => 'max:50|nullable',
            'city' => 'required|max:50',
            'address' => 'required|max:100',
        ];
    }
}

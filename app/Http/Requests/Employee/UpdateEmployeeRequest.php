<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = $employee instanceof Employee ? $employee->id : null;
        $experienceOptions = Employee::EXPERIENCE_OPTIONS;

        if ($employee instanceof Employee && $employee->experience && ! in_array($employee->experience, $experienceOptions, true)) {
            $experienceOptions[] = $employee->experience;
        }

        return [
            'photo' => 'image|file|max:1024',
            'name' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:50', Rule::unique('employees')->ignore($employeeId)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employeeId)],
            'experience' => ['nullable', Rule::in($experienceOptions)],
            'salary' => 'numeric',
            'vacation' => 'max:50|nullable',
            'city' => 'max:50',
            'address' => 'required|max:100',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Hospital profile fields, shared by hospital registration and the hospital's own profile page.
 */
class HospitalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hospital_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'hospital_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'license_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'hospital_name' => 'hospital name',
            'hospital_phone' => 'hospital phone',
        ];
    }

    /**
     * Validated input mapped onto Hospital columns.
     *
     * @return array<string, string|null>
     */
    public function hospitalAttributes(): array
    {
        return [
            'name' => $this->validated('hospital_name'),
            'address' => $this->validated('address'),
            'city' => $this->validated('city'),
            'phone' => $this->validated('hospital_phone'),
            'license_number' => $this->validated('license_number'),
        ];
    }
}

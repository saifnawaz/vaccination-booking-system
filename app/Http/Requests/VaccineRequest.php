<?php

namespace App\Http\Requests;

use App\Models\Vaccine;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VaccineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controllers authorize through VaccinePolicy
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vaccine = $this->route('vaccine');

        return [
            'name' => ['required', 'string', 'max:255'],
            // Unique against deleted vaccines too: a code is never reused.
            'code' => [
                'required', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/',
                Rule::unique(Vaccine::class, 'code')->ignore($vaccine instanceof Vaccine ? $vaccine->id : null),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The code may only contain capital letters, numbers and dashes.',
        ];
    }
}

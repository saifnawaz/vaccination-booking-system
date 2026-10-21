<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Models\Child;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controllers authorize through ChildPolicy
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $oldest = now()->subYears(config('vaccination.max_child_age_years'))->toDateString();

        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today', "after_or_equal:{$oldest}"],
            'gender' => ['required', Rule::enum(Gender::class)],
            'blood_group' => ['nullable', Rule::in(Child::BLOOD_GROUPS)],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before_or_equal' => 'The date of birth cannot be in the future.',
            'date_of_birth.after_or_equal' => 'Children older than '.config('vaccination.max_child_age_years').' years cannot be added.',
        ];
    }

    /**
     * When correcting a date of birth, it cannot move after a dose that was already given.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $child = $this->route('child');

                if (! $child instanceof Child || $validator->errors()->has('date_of_birth')) {
                    return;
                }

                $firstGiven = $child->doses()->whereNotNull('administered_at')->min('administered_at');

                if ($firstGiven && $this->date('date_of_birth')->gt($firstGiven)) {
                    $validator->errors()->add(
                        'date_of_birth',
                        'The date of birth cannot be after the first recorded dose ('.date('j M Y', strtotime($firstGiven)).').'
                    );
                }
            },
        ];
    }
}

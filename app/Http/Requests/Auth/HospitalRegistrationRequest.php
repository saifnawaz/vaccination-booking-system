<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\HospitalProfileRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules;

class HospitalRegistrationRequest extends HospitalProfileRequest
{
    /**
     * Contact person's login account plus the hospital profile.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ...parent::rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'contact person name',
            ...parent::attributes(),
        ];
    }
}

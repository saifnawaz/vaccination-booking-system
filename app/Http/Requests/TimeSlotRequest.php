<?php

namespace App\Http\Requests;

use App\Models\TimeSlot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * A single new slot.
 */
class TimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controllers authorize through TimeSlotPolicy
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['required', 'integer', 'min:1', 'max:'.config('vaccination.slots.max_capacity')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'Slots can only be created for today or later.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->date('date')->isToday() && $this->input('start_time') <= now()->format('H:i')) {
                    $validator->errors()->add('start_time', 'This time has already passed today.');

                    return;
                }

                $overlaps = TimeSlot::overlapping(
                    $this->user()->hospital->id,
                    $this->input('date'),
                    $this->input('start_time').':00',
                    $this->input('end_time').':00',
                )->exists();

                if ($overlaps) {
                    $validator->errors()->add('start_time', 'This overlaps a slot you already have on that day.');
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Bulk slot creation: "every Mon–Sat from 1 to 31 Dec, 9:00–13:00, 30-minute slots, 5 children each".
 */
class BulkTimeSlotRequest extends FormRequest
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
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'], // ISO: 1 = Monday … 7 = Sunday
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['required', 'integer', Rule::in(config('vaccination.slots.lengths'))],
            'capacity' => ['required', 'integer', 'min:1', 'max:'.config('vaccination.slots.max_capacity')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_from.after_or_equal' => 'Slots can only be created for today or later.',
            'date_to.after_or_equal' => 'The last day must be on or after the first day.',
            'weekdays.required' => 'Choose at least one day of the week.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date_from' => 'first day',
            'date_to' => 'last day',
            'slot_minutes' => 'slot length',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $maxDays = config('vaccination.slots.bulk_max_range_days');

                if ($this->date('date_from')->diffInDays($this->date('date_to')) + 1 > $maxDays) {
                    $validator->errors()->add('date_to', "You can create slots for up to {$maxDays} days at a time.");
                }

                $start = strtotime($this->input('start_time'));
                $end = strtotime($this->input('end_time'));

                if ($end - $start < $this->integer('slot_minutes') * 60) {
                    $validator->errors()->add('end_time', 'The opening hours are shorter than one slot.');
                }
            },
        ];
    }
}

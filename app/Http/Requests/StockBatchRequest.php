<?php

namespace App\Http\Requests;

use App\Models\HospitalStock;
use App\Models\Vaccine;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Adding a new batch. Adjusting an existing batch only changes its quantity
 * (see StockController::update).
 */
class StockBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controllers authorize through HospitalStockPolicy
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['batch_number' => strtoupper(trim((string) $this->input('batch_number')))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hospitalId = $this->user()->hospital->id;

        return [
            // Only active, non-deleted vaccines can receive new stock.
            'vaccine_id' => [
                'required', 'integer',
                Rule::exists(Vaccine::class, 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'batch_number' => [
                'required', 'string', 'max:50', 'regex:/^[A-Z0-9\-\/]+$/',
                Rule::unique(HospitalStock::class)
                    ->where('hospital_id', $hospitalId)
                    ->where('vaccine_id', $this->integer('vaccine_id')),
            ],
            'expiry_date' => ['required', 'date', 'after:today'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vaccine_id.exists' => 'Choose a vaccine from the catalog.',
            'batch_number.unique' => 'You already have a batch with this number for this vaccine. Adjust that batch instead.',
            'batch_number.regex' => 'Batch numbers may only contain letters, numbers, dashes and slashes.',
            'expiry_date.after' => 'An expired batch cannot be added. The expiry date must be in the future.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['vaccine_id' => 'vaccine'];
    }
}

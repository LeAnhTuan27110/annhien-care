<?php

namespace App\Domains\Health\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['patient', 'family_member']) ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => [
                // A family member cannot rely on an implicit patient context when creating a medical record.
                Rule::requiredIf(fn () => $this->user()?->hasRole('family_member') ?? false),
                'nullable', 'integer', 'exists:users,id',
            ],
            'drug_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'instructions' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

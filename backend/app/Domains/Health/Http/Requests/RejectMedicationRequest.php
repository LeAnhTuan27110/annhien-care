<?php

namespace App\Domains\Health\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:5000'],
        ];
    }
}

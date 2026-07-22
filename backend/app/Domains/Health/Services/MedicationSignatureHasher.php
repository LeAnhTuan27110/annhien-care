<?php

namespace App\Domains\Health\Services;

use App\Domains\Health\Models\Medication;
use App\Models\User;

class MedicationSignatureHasher
{
    public function for(Medication $medication, User $doctor): string
    {
        // Sign the clinical fields and final decision so the signature represents one exact reviewed version.
        $payload = [
            'medication_id' => $medication->id,
            'patient_id' => $medication->patient_id,
            'drug_name' => $medication->drug_name,
            'dosage' => $medication->dosage,
            'frequency' => $medication->frequency,
            'route' => $medication->route,
            'start_date' => $medication->start_date?->toDateString(),
            'end_date' => $medication->end_date?->toDateString(),
            'verified_by' => $doctor->id,
            'verified_at' => $medication->verified_at?->toIso8601String(),
            'verification_status' => $medication->verification_status,
            'rejection_reason' => $medication->rejection_reason,
        ];

        // HMAC prevents a database-only change from producing a valid replacement signature.
        return hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), config('app.key'));
    }
}

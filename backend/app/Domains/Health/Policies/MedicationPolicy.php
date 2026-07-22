<?php

namespace App\Domains\Health\Policies;

use App\Domains\Auth\Models\FamilyLink;
use App\Domains\Health\Models\Medication;
use App\Models\User;

class MedicationPolicy
{
    public function createForPatient(User $user, int $patientId): bool
    {
        if ($user->id === $patientId && $user->hasRole('patient')) {
            return true;
        }

        // Only a full, active delegation can create medical records on a patient's behalf.
        return $user->hasRole('family_member') && $this->hasFamilyAccess($user, $patientId, ['full']);
    }

    public function view(User $user, Medication $medication): bool
    {
        return $medication->patient_id === $user->id
            || ($user->hasRole('family_member') && $this->hasFamilyAccess($user, $medication->patient_id, ['full', 'view_only']));
    }

    private function hasFamilyAccess(User $user, int $patientId, array $permissionLevels): bool
    {
        // Revoked and pending consent links must never grant access to health records.
        return FamilyLink::query()
            ->where('patient_id', $patientId)
            ->where('family_user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('permission_level', $permissionLevels)
            ->exists();
    }
}

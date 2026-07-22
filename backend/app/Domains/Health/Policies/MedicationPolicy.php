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
            || $this->isVerifiedClinician($user)
            || ($user->hasRole('family_member') && $this->hasFamilyAccess($user, $medication->patient_id, ['full', 'view_only']));
    }

    public function viewPendingQueue(User $user): bool
    {
        return $this->isVerifiedClinician($user);
    }

    public function verify(User $user, Medication $medication): bool
    {
        return $this->isVerifiedClinician($user);
    }

    public function reject(User $user, Medication $medication): bool
    {
        return $this->isVerifiedClinician($user);
    }

    private function isVerifiedClinician(User $user): bool
    {
        // A role alone is insufficient for clinical verification; professional credentials must be verified too.
        $isVerifiedDoctor = $user->hasRole('doctor')
            && $user->doctorProfile()->where('license_verified_status', 'verified')->exists();

        $isVerifiedNurse = $user->hasRole('nurse')
            && $user->caregiverProfile()
                ->where('caregiver_type', 'nurse')
                ->where('license_verified_status', 'verified')
                ->exists();

        return $isVerifiedDoctor || $isVerifiedNurse;
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

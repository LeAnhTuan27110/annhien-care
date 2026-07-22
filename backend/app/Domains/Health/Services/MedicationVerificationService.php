<?php

namespace App\Domains\Health\Services;

use App\Domains\Health\Models\Medication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MedicationVerificationService
{
    public function __construct(private readonly MedicationSignatureHasher $signatureHasher) {}

    public function verify(Medication $medication, User $doctor, string $ipAddress, ?string $deviceInfo): Medication
    {
        return $this->review($medication, $doctor, 'verified', null, $ipAddress, $deviceInfo);
    }

    public function reject(
        Medication $medication,
        User $clinician,
        string $rejectionReason,
        string $ipAddress,
        ?string $deviceInfo,
    ): Medication {
        return $this->review($medication, $clinician, 'rejected', $rejectionReason, $ipAddress, $deviceInfo);
    }

    private function review(
        Medication $medication,
        User $clinician,
        string $status,
        ?string $rejectionReason,
        string $ipAddress,
        ?string $deviceInfo,
    ): Medication {
        return DB::transaction(function () use ($medication, $clinician, $status, $rejectionReason, $ipAddress, $deviceInfo): Medication {
            // Serialize concurrent reviews so a medication cannot receive two final decisions or signatures.
            $medication = Medication::query()->lockForUpdate()->findOrFail($medication->id);

            if ($medication->verification_status !== 'pending') {
                throw ValidationException::withMessages([
                    'medication' => ['Bản ghi thuốc này đã được xử lý.'],
                ]);
            }

            $medication->update([
                'verification_status' => $status,
                'verified_by' => $clinician->id,
                'verified_at' => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            $medication->verificationSignatures()->create([
                'verified_by' => $clinician->id,
                'status' => $status,
                'signature_hash' => $this->signatureHasher->for($medication, $clinician),
                'signed_at' => $medication->verified_at,
                'rejection_reason' => $rejectionReason,
                'ip_address' => $ipAddress,
                'device_info' => Str::limit((string) $deviceInfo, 255),
            ]);

            // Keep a separate, immutable audit event for the clinical decision.
            activity('medication')
                ->performedOn($medication)
                ->causedBy($clinician)
                ->event("medication.{$status}")
                ->log("Medication {$status} by clinician");

            return $medication->fresh();
        });
    }
}

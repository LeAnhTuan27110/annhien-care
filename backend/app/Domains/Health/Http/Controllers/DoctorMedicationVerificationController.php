<?php

namespace App\Domains\Health\Http\Controllers;

use App\Domains\Health\Contracts\MedicationVerificationControllerInterface;
use App\Domains\Health\Http\Requests\RejectMedicationRequest;
use App\Domains\Health\Http\Resources\MedicationResource;
use App\Domains\Health\Models\Medication;
use App\Domains\Health\Services\MedicationVerificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorMedicationVerificationController extends Controller implements MedicationVerificationControllerInterface
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewPendingQueue', Medication::class);

        $medications = Medication::query()
            ->where('verification_status', 'pending')
            ->latest('created_at')
            ->paginate(25);

        return MedicationResource::collection($medications);
    }

    public function verify(
        Request $request,
        Medication $medication,
        MedicationVerificationService $verificationService,
    ): MedicationResource {
        $this->authorize('verify', $medication);

        $medication = $verificationService->verify(
            medication: $medication,
            doctor: $request->user(),
            ipAddress: (string) $request->ip(),
            deviceInfo: $request->userAgent(),
        );

        return new MedicationResource($medication);
    }

    public function reject(
        RejectMedicationRequest $request,
        Medication $medication,
        MedicationVerificationService $verificationService,
    ): MedicationResource {
        $this->authorize('reject', $medication);

        $medication = $verificationService->reject(
            medication: $medication,
            clinician: $request->user(),
            rejectionReason: $request->string('rejection_reason')->toString(),
            ipAddress: (string) $request->ip(),
            deviceInfo: $request->userAgent(),
        );

        return new MedicationResource($medication);
    }
}

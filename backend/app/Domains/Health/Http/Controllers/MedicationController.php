<?php

namespace App\Domains\Health\Http\Controllers;

use App\Domains\Health\Contracts\MedicationControllerInterface;
use App\Domains\Health\Http\Requests\StoreMedicationRequest;
use App\Domains\Health\Http\Resources\MedicationStatusResource;
use App\Domains\Health\Models\Medication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationController extends Controller implements MedicationControllerInterface
{
    public function store(StoreMedicationRequest $request): JsonResponse
    {
        // Patients always create records for themselves; family members must explicitly name the linked patient.
        $patientId = $request->user()->hasRole('patient')
            ? $request->user()->id
            : (int) $request->validated('patient_id');

        // The policy enforces an active family link with the required permission level.
        $this->authorize('createForPatient', [Medication::class, $patientId]);

        $medication = Medication::query()->create([
            ...$request->validated(),
            'patient_id' => $patientId,
            'created_by' => $request->user()->id,
            'source' => 'manual',
            // User-submitted medical data must enter the professional review workflow first.
            'verification_status' => 'pending',
        ]);

        return (new MedicationStatusResource($medication))->response()->setStatusCode(201);
    }

    public function show(Request $request, Medication $medication): MedicationStatusResource
    {
        $this->authorize('view', $medication);

        return new MedicationStatusResource($medication);
    }
}

<?php

namespace App\Domains\Health\Contracts;

use App\Domains\Health\Http\Requests\RejectMedicationRequest;
use App\Domains\Health\Http\Resources\MedicationResource;
use App\Domains\Health\Models\Medication;
use App\Domains\Health\Services\MedicationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * HTTP contract for clinical medication review commands.
 *
 * Queue retrieval uses the REST index action; review commands use explicit domain verbs.
 */
interface MedicationVerificationControllerInterface
{
    public function index(Request $request): AnonymousResourceCollection;

    public function verify(
        Request $request,
        Medication $medication,
        MedicationVerificationService $verificationService,
    ): MedicationResource;

    public function reject(
        RejectMedicationRequest $request,
        Medication $medication,
        MedicationVerificationService $verificationService,
    ): MedicationResource;
}

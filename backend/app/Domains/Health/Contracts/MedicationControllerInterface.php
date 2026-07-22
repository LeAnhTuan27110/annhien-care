<?php

namespace App\Domains\Health\Contracts;

use App\Domains\Health\Http\Requests\StoreMedicationRequest;
use App\Domains\Health\Http\Resources\MedicationStatusResource;
use App\Domains\Health\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** HTTP contract for patient and family medication resource actions. */
interface MedicationControllerInterface
{
    public function store(StoreMedicationRequest $request): JsonResponse;

    public function show(Request $request, Medication $medication): MedicationStatusResource;
}

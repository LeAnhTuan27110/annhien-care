<?php

namespace App\Domains\Health\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'verification_status' => $this->verification_status,
            'submitted_at' => $this->created_at?->toIso8601String(),
        ];

        if ($this->verification_status === 'verified') {
            return [...$data, ...MedicationResource::make($this->resource)->resolve($request)];
        }

        // Do not disclose unverified medical content to patients or family members.
        return $data;
    }
}

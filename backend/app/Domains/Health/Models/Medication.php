<?php

namespace App\Domains\Health\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id', 'drug_name', 'dosage', 'frequency', 'route', 'start_date', 'end_date',
        'prescribing_doctor_id', 'instructions', 'source', 'verification_status', 'verified_by', 'verified_at',
        'rejection_reason', 'created_by',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'verified_at' => 'datetime'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verificationSignatures(): MorphMany
    {
        return $this->morphMany(VerificationSignature::class, 'verifiable');
    }
}

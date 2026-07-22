<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Records consented access between a patient and a family-member account. */
class FamilyLink extends Model
{
    /** Link attributes that may be supplied by the authorized workflow. */
    protected $fillable = [
        'patient_id',
        'family_user_id',
        'relationship_type',
        'permission_level',
        'consent_document_url',
        'consented_at',
        'status',
        'created_by',
    ];

    /** Converts the recorded consent timestamp to a date-time value. */
    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
        ];
    }

    /** Returns the patient whose information is shared through this link. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** Returns the family-member account granted access by this link. */
    public function familyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'family_user_id');
    }

    /** Returns the user who created this access link. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

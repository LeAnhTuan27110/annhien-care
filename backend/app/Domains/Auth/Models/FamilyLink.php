<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyLink extends Model
{
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

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function familyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'family_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Stores a doctor's credentials, consultation settings, and clinical permissions. */
class DoctorProfile extends Model
{
    /** Attributes managed through doctor profile creation and updates. */
    protected $fillable = [
        'user_id',
        'license_number',
        'specialty',
        'hospital_affiliation',
        'license_verified_status',
        'license_verified_by',
        'license_verified_at',
        'consultation_fee',
        'can_author_alert_rules',
    ];

    /** Converts credential timestamps, fees, and permissions to application types. */
    protected function casts(): array
    {
        return [
            'license_verified_at' => 'datetime',
            'consultation_fee' => 'decimal:2',
            'can_author_alert_rules' => 'boolean',
        ];
    }

    /** Returns the account that owns this doctor profile. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Returns the user who verified the doctor's professional license. */
    public function licenseVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'license_verified_by');
    }
}

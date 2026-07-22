<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Stores professional, service-area, and verification details for a caregiver. */
class CaregiverProfile extends Model
{
    /** Attributes managed through caregiver profile creation and updates. */
    protected $fillable = [
        'user_id',
        'caregiver_type',
        'license_number',
        'license_verified_status',
        'license_verified_by',
        'license_verified_at',
        'years_experience',
        'bio',
        'skills',
        'hourly_rate',
        'service_radius_km',
        'base_latitude',
        'base_longitude',
        'background_check_status',
        'rating_avg',
        'rating_count',
    ];

    /** Preserves precision for pricing/location data and decodes structured skills. */
    protected function casts(): array
    {
        return [
            'license_verified_at' => 'datetime',
            'skills' => 'array',
            'hourly_rate' => 'decimal:2',
            'base_latitude' => 'decimal:7',
            'base_longitude' => 'decimal:7',
            'rating_avg' => 'decimal:2',
        ];
    }

    /** Returns the account that owns this caregiver profile. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Returns the user who verified the caregiver's professional license. */
    public function licenseVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'license_verified_by');
    }
}

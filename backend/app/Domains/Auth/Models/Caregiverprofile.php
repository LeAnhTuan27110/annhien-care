<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaregiverProfile extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function licenseVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'license_verified_by');
    }
}
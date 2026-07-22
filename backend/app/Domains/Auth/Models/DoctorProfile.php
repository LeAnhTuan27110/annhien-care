<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorProfile extends Model
{
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

    protected function casts(): array
    {
        return [
            'license_verified_at' => 'datetime',
            'consultation_fee' => 'decimal:2',
            'can_author_alert_rules' => 'boolean',
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
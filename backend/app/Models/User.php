<?php

namespace App\Models;

use App\Domains\Auth\Models\CaregiverProfile;
use App\Domains\Auth\Models\DoctorProfile;
use App\Domains\Auth\Models\FamilyLink;
use App\Domains\Auth\Models\PatientProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** Provides API tokens, roles, notifications, factories, and soft deletion. */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    
    // Attributes that can be mass assigned.
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'locale',
    ];
    // Sensitive attributes hidden from JSON responses.
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /** Converts persisted account and security attributes to their application types. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'mfa_secret' => 'encrypted',
            'password' => 'hashed',
        ];
    }

    /** Returns the patient-specific profile owned by this account. */
    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientProfile::class);
    }

    /** Returns the caregiver-specific profile owned by this account. */
    public function caregiverProfile(): HasOne
    {
        return $this->hasOne(CaregiverProfile::class);
    }

    /** Returns the doctor-specific profile owned by this account. */
    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    /** Patients linked to this user as a family member. */
    public function familyLinksAsFamily(): HasMany
    {
        return $this->hasMany(FamilyLink::class, 'family_user_id');
    }

    /** Family members linked to this user as a patient. */
    public function familyLinksAsPatient(): HasMany
    {
        return $this->hasMany(FamilyLink::class, 'patient_id');
    }
}

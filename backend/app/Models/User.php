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
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    
    // Các trường có thể gán giá trị hàng loạt (mass assignable)
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'locale',
    ];
    // tu dong an cac truong nhay cam khi tra ve json
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

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

    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientProfile::class);
    }

    public function caregiverProfile(): HasOne
    {
        return $this->hasOne(CaregiverProfile::class);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    /** Các bệnh nhân mà user này (người nhà) được liên kết tới. */
    public function familyLinksAsFamily(): HasMany
    {
        return $this->hasMany(FamilyLink::class, 'family_user_id');
    }

    /** Các người nhà được liên kết tới user này (khi user là bệnh nhân). */
    public function familyLinksAsPatient(): HasMany
    {
        return $this->hasMany(FamilyLink::class, 'patient_id');
    }
}
<?php
namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Stores patient demographics and baseline care information. */
class PatientProfile extends Model
{
    /** Attributes managed through patient profile creation and updates. */
    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'national_id',
        'address',
        'city',
        'district',
        'blood_type',
        'height_cm',
        'weight_kg',
        'primary_condition_summary',
        'care_level',
    ];

    /** Encrypts government ID data and preserves measurement precision. */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'national_id' => 'encrypted',
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
        ];
    }

    /** Returns the user account that owns this patient profile. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
?>

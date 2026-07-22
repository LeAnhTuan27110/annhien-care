<?php

namespace App\Domains\Health\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerificationSignature extends Model
{
    protected $fillable = [
        'verified_by', 'status', 'signature_hash', 'signed_at', 'rejection_reason', 'ip_address', 'device_info',
    ];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

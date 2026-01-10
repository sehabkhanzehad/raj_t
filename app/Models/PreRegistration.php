<?php

namespace App\Models;

use App\Enums\PreRegistrationStatus;
use App\Models\Traits\HasPassport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PreRegistration extends Model
{
    use HasPassport;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'archive_date' => 'date',
        'status' => PreRegistrationStatus::class,
    ];

    // Relationships
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function groupLeader(): BelongsTo
    {
        return $this->belongsTo(GroupLeader::class);
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereStatus(PreRegistrationStatus::Active);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === PreRegistrationStatus::Active;
    }
}

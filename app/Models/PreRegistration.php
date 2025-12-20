<?php

namespace App\Models;

use App\Enums\PreRegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreRegistration extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'registration_date' => 'date',
        'archive_date' => 'date',
        'status' => PreRegistrationStatus::class,
    ];
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

    // Scopes

    public function scopeActive($query)
    {
        return $query->whereStatus(PreRegistrationStatus::Active);
    }
}

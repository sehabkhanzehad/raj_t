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

    public function replaceOld(): HasOne
    {
        return $this->hasOne(Replace::class, 'old_pre_registration_id');
    }

    public function replaceNew(): HasOne
    {
        return $this->hasOne(Replace::class, 'new_pre_registration_id');
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

    public function isPending(): bool
    {
        return $this->status === PreRegistrationStatus::Pending;
    }

    public function isRegistered(): bool
    {
        return $this->status === PreRegistrationStatus::Registered && $this->registration()->exists();
    }

    public function markAsActive(): void
    {
        $this->status = PreRegistrationStatus::Active;
        $this->save();
    }

    public function markAsCancelled($date = null): void
    {
        $this->status = PreRegistrationStatus::Cancelled;
        $this->cancel_date = $date ?? now();
        $this->save();
    }

    public function markAsRegistered(): void
    {
        $this->status = PreRegistrationStatus::Registered;
        $this->save();
    }

    public function hasReplacement(): bool
    {
        return $this->replaceOld()->exists();
    }
}

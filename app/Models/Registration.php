<?php

namespace App\Models;

use App\Enums\PilgrimLogType;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Registration extends Model
{
    protected $casts = [
        "date" => 'date',
        "status" => RegistrationStatus::class,
        "passport_expiry_date" => 'date',
    ];

    protected $guarded = ['id'];

    // Relations
    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    public function preRegistration(): BelongsTo
    {
        return $this->belongsTo(PreRegistration::class);
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function replace(): HasOne
    {
        return $this->hasOne(Replace::class);
    }

    public function logs(): MorphMany
    {
        return $this->morphMany(PilgrimLog::class, 'reference', 'reference_type', 'reference_id');
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === RegistrationStatus::Active;
    }

    public function hasReplace(): bool
    {
        return $this->replace()->exists();
    }

    public function restoreToPreRegistration(): bool //!Note: Only for tinker
    {
        $this->preRegistration->markAsActive();
        // $this->logs()->where('type', PilgrimLogType::HajjRegistered)->delete();
        return $this->delete();
    }

    // scopes
    public function scopeCurrentYear($query)
    {
        $currentYear = Year::getCurrentYear();
        if ($currentYear) return $query->where('year_id', $currentYear->id);

        return $query;
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $model) {
            $model->year_id = Year::getCurrentYear()?->id;
        });
    }
}

<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $casts = [
        "date" => 'date',
        "status" => RegistrationStatus::class,
        "passport_expiry_date" => 'date',
    ];

    protected $guarded = ['id'];

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

    // scopes
    public function scopeCurrentYear($query)
    {
        $currentYear = Year::getCurrentYear();
        if ($currentYear) return $query->where('year_id', $currentYear->id);

        return $query;
    }

    protected static function booted()
    {
        static::creating(function (Registration $model) {
            $model->year_id = Year::getCurrentYear()?->id;
        });
    }
}

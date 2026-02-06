<?php

namespace App\Models;

use App\Models\Traits\BelongsToAgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Year extends Model
{
    use BelongsToAgency;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'boolean',
    ];

    protected $guarded = ['id'];

    // Relations
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status;
    }
    public static function getCurrentYear(): ?Year
    {
        return self::active()->first();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}

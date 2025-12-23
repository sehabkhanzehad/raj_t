<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Year extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'boolean',
    ];

    protected $guarded = ['id'];

    // Relationships
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getCurrentYear(): ?Year
    {
        return self::active()->first();
    }
}

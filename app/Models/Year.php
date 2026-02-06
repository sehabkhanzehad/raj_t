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
        'default' => 'boolean',
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
    public function isDefault(): bool
    {
        return $this->default;
    }

    public static function getDefaultYear(): ?Year
    {
        return self::default()->first();
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }
}

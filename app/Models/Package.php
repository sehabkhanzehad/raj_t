<?php

namespace App\Models;

use App\Enums\PackageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    protected $casts = [
        'type' => PackageType::class,
        'status' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $guarded = ['id'];

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeHajj($query)
    {
        return $query->where('type', PackageType::Hajj);
    }

    public function scopeUmrah($query)
    {
        return $query->where('type', PackageType::Umrah);
    }

    // Helpers
    public function isHajj(): bool
    {
        return $this->type === PackageType::Hajj;
    }

    public function isUmrah(): bool
    {
        return $this->type === PackageType::Umrah;
    }

    protected static function booted()
    {
        static::creating(function (Package $model) {
            $model->year_id = Year::getCurrentYear()?->id;
        });
    }
}

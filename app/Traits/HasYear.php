<?php

namespace App\Traits;

use App\Models\Year;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasYear
{
    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    protected static function bootHasYear(): void
    {
        static::creating(function ($model) {
            $model->year_id = currentYear()?->id;
        });
    }
}

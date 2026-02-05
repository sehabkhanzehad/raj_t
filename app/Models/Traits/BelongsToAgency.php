<?php

namespace App\Models\Traits;

use App\Models\Agency;
use App\Models\Scopes\AgencyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToAgency
{
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function belongsToCurrentAgency(): bool
    {
        return $this->agency_id === currentAgency()->id;
    }

    protected static function bootBelongsToAgency(): void
    {
        if (!currentAgency()) return;

        static::addGlobalScope(new AgencyScope());

        static::creating(fn(Model $model) => $model->agency_id = currentAgency()->id);
    }
}

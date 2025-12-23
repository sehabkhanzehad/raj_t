<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $casts = [
        'account_type' => AccountType::class,
    ];

    protected $guarded = [
        'id',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function preRegistrations(): HasMany
    {
        return $this->hasMany(PreRegistration::class);
    }
}

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
        'status' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}

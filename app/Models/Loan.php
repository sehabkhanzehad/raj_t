<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Loan extends Model
{
    protected $casts = [
        'status' => LoanStatus::class,
        'date' => 'date',
    ];

    protected $guarded = ['id'];

    // Relations
    public function loanable(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }


    // Helpers
    public function isLend(): bool
    {
        return $this->direction === 'lend';
    }

    public function getSection(): Section
    {
        return $this->isLend() ? Section::typeLend()->first() : Section::typeBorrow()->first();
    }

    // Scopes
    public function scopeLend($query)
    {
        return $query->where('direction', 'lend');
    }

    public function scopeBorrow($query)
    {
        return $query->where('direction', 'borrow');
    }
}

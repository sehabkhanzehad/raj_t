<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    protected static function booted()
    {
        static::creating(function (Transaction $model) {
            $model->year_id = Year::getCurrentYear()?->id;
        });
    }
}

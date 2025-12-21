<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Loan extends Model
{
    protected $casts = [
        'status' => LoanStatus::class,
    ];

    protected $guarded = ['id'];

    public function loanable(): MorphTo
    {
        return $this->morphTo();
    }
}

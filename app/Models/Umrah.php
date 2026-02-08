<?php

namespace App\Models;

use App\Enums\UmrahStatus;
use App\Models\Traits\BelongsToAgency;
use App\Models\Traits\HasPassport;
use App\Traits\HasYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Transaction;

class Umrah extends Model
{
    use BelongsToAgency,
        HasPassport,
        HasYear;

    protected $casts = [
        'status' => UmrahStatus::class,
    ];

    protected $guarded = ['id'];

    public function groupLeader(): BelongsTo
    {
        return $this->belongsTo(GroupLeader::class);
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function references(): MorphMany
    {
        return $this->morphMany(Reference::class, 'referenceable');
    }

    public function totalCollect(): float
    {
        return Transaction::whereHas('references', function ($query) {
            $query->where('referenceable_id', $this->id)
                ->where('referenceable_type', self::class);
        })->where('type', 'income')->sum('amount');
    }

    public function totalRefund(): float
    {
        return Transaction::whereHas('references', function ($query) {
            $query->where('referenceable_id', $this->id)
                ->where('referenceable_type', self::class);
        })->where('type', 'expense')->sum('amount');
    }

    public function dueAmount(): float
    {
        $due = $this->package->price - ($this->totalPaid() + $this->discount);
        return max($due, 0);
    }

    public function totalPaid(): float
    {
        $totalPaid = $this->totalCollect() - $this->totalRefund();
        return $totalPaid;
    }
}

<?php

namespace App\Models;

use App\Enums\UmrahStatus;
use App\Models\Traits\HasPassport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Transaction;

class Umrah extends Model
{
    use HasPassport;

    protected $casts = [
        'status' => UmrahStatus::class,
    ];

    protected $guarded = ['id'];

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

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

    protected static function booted(): void
    {
        static::creating(function (Umrah $model) {
            $model->year_id = Year::getCurrentYear()?->id;
        });
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
        $totalPaid = $this->totalCollect() - $this->totalRefund();
        $due = $this->package->price - ($totalPaid + $this->discount);
        return max($due, 0);
    }

    public function totalPaid(): float
    {
        $totalPaid = $this->totalCollect() - $this->totalRefund();
        return $totalPaid;
    }
}

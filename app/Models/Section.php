<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\SectionType;
use App\Http\Requests\Api\TransactionRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Section extends Model
{
    protected $casts = [
        'type' => SectionType::class,
    ];

    protected $guarded = [
        'id',
    ];

    public function bank(): HasOne
    {
        return $this->hasOne(Bank::class);
    }

    public function groupLeader(): HasOne
    {
        return $this->hasOne(GroupLeader::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function lastTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    // Scopes
    public function scopeTypeBank($query)
    {
        return $query->whereType(SectionType::Bank);
    }

    public function scopeTypeGroupLeader($query)
    {
        return $query->whereType(SectionType::GroupLeader);
    }

    public function scopeTypeEmployee($query)
    {
        return $query->whereType(SectionType::Employee);
    }

    public function scopeTypeBill($query)
    {
        return $query->whereType(SectionType::Bill);
    }

    public function scopeTypeLend($query)
    {
        return $query->whereType(SectionType::Lend);
    }

    public function scopeTypeBorrow($query)
    {
        return $query->whereType(SectionType::Borrow);
    }

    public function scopeTypeOther($query)
    {
        return $query->whereType(SectionType::Other);
    }

    // Helpers
    public function currentBalance(): float
    {
        return $this->lastTransaction?->after_balance ?? 0.0;
    }

    public function afterBalance(TransactionRequest $request): float
    {
        // if lend or expense then add
        // if lend or income then subtract

        // if borrow or income then add
        // if borrow or expense then subtract

        if ($this->isType(SectionType::Lend)) {
            if ($request->isIncome()) {
                $loan = $request->loan();
                $loan->increment('paid_amount', $request->amount);
                if ($loan->amount <= $loan->paid_amount) {
                    $loan->update(['status' => LoanStatus::Paid]);
                } else {
                    $loan->update(['status' => LoanStatus::Partial]);
                }
                return $this->currentBalance() - $request->amount;
            } else {
                return $this->currentBalance() + $request->amount;
            }
        } elseif ($this->isType(SectionType::Borrow)) {
            if ($request->isIncome()) {
                return $this->currentBalance() + $request->amount;
            } else {
                return $this->currentBalance() - $request->amount;
            }
        }

        return $request->isIncome()
            ? $this->currentBalance() - $request->amount
            : $this->currentBalance() + $request->amount;
    }

    public function isType(SectionType $type): bool
    {
        return $this->type === $type;
    }

    public function isRegistration(): bool
    {
        return $this->isType(SectionType::Registration);
    }

    public function isPreRegistration(): bool
    {
        return $this->isType(SectionType::PreRegistration);
    }

    public function isloan(): bool
    {
        return $this->isType(SectionType::Lend) || $this->isType(SectionType::Borrow);
    }

    public function isGroupLeader(): bool
    {
        return $this->isType(SectionType::GroupLeader);
    }

    public function needToAddReferences(): bool
    {
        return in_array($this->type, [
            SectionType::Registration,
            SectionType::PreRegistration,
            SectionType::Lend,
            SectionType::Borrow,
        ]);
    }
}

<?php

namespace App\Models;

use App\Enums\SectionType;
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
}

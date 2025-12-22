<?php

namespace App\Models;

use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Model;
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

    // Scopes
    public function scopeTypeBank($query)
    {
        return $query->whereType(SectionType::Bank);
    }

    public function scopeTypeGroupLeader($query)
    {
        return $query->whereType(SectionType::GroupLeader);
    }
}

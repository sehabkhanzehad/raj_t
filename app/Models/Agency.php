<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'name',
        'bangla_name',
        'arabic_name',
        'license',
        'logo',
        'address',
        'phone',
        'email',
    ];

    // Relations
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(Customer::class, 'agency_id', 'id');
    }

    // Helpers
    public function canAccess(Customer $customer): bool
    {
        return $customer->isOwner() ? $this->owner()->is($customer) : $customer->customerAgency()->is($this);
    }
}

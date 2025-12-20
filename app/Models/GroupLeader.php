<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupLeader extends Model
{
    protected $guarded = ['id'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class, 'pilgrim_id');
    }

    public function activePreRegistrations(): HasMany
    {
        return $this->hasMany(PreRegistration::class)->active();
    }

    // এই লিডারের কাছ থেকে যারা অন্য লিডারের কাছে চলে গেছে (History)
    // public function transferredOut()
    // {
    //     return $this->hasMany(RegistrationTransfer::class, 'from_leader_id');
    // }

    // অন্য লিডারের কাছ থেকে যারা এই লিডারের আন্ডারে এসেছে (History)
    // public function transferredIn()
    // {
    //     return $this->hasMany(RegistrationTransfer::class, 'to_leader_id');
    // }
}

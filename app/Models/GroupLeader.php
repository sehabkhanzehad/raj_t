<?php

namespace App\Models;

use App\Models\Traits\BelongsToAgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class GroupLeader extends Model
{
    use BelongsToAgency;

    protected $casts = [
        'status' => 'boolean',
        'pilgrim_required' => 'boolean',
    ];
    protected $guarded = ['id'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activePreRegistrations(): HasMany
    {
        return $this->preRegistrations()->active();
    }

    public function preRegistrations(): HasMany // All Pre-Registrations
    {
        return $this->hasMany(PreRegistration::class);
    }

    public function registrations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Registration::class,
            PreRegistration::class,
            'group_leader_id', // Foreign key on pre_registrations table
            'pre_registration_id', // Foreign key on registrations table
            'id', // Local key on group_leaders table
            'id' // Local key on pre_registrations table
        );
    }

    public function pilgrims(): HasManyThrough
    {
        return $this->hasManyThrough(
            Pilgrim::class,
            PreRegistration::class,
            'group_leader_id', // Foreign key on pre_registrations table
            'id',              // Foreign key on pilgrims table
            'id',              // Local key on group_leaders table
            'pilgrim_id'       // Local key on pre_registrations table
        );
    }


    // এই লিডারের কাছ থেকে যারা অন্য লিডারের কাছে চলে গেছে (History)
    // public function transferredOut()
    // {s
    //     return $this->hasMany(RegistrationTransfer::class, 'from_leader_id');
    // }

    // অন্য লিডারের কাছ থেকে যারা এই লিডারের আন্ডারে এসেছে (History)
    // public function transferredIn()
    // {
    //     return $this->hasMany(RegistrationTransfer::class, 'to_leader_id');
    // }
}

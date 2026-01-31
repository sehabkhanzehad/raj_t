<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pilgrim extends Model
{
    protected $guarded = ['id'];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preRegistration(): HasOne // Current/Latest Pre-Registration
    {
        return $this->hasOne(PreRegistration::class)->where('status', 'active')->latestOfMany();
    }

    public function preRegistrations(): HasMany // All Pre-Registrations
    {
        return $this->hasMany(PreRegistration::class);
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class)->latestOfMany();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function passports(): HasMany
    {
        return $this->hasMany(Passport::class);
    }

    public function currentPassport(): HasOne
    {
        return $this->hasOne(Passport::class)->latestOfMany();
    }

    public function pilgrimLogs(): HasMany
    {
        return $this->hasMany(PilgrimLog::class);
    }


    // public function groupLeader()
    // {
    //     return $this->hasOneThrough(
    //         GroupLeader::class,
    //         PreRegistration::class,
    //         'pilgrim_id', // Foreign key on PreRegistration table...
    //         'id', // Foreign key on GroupLeader table...
    //         'id', // Local key on Pilgrim table...
    //         'group_leader_id' // Local key on PreRegistration table...
    //     )->latestOfMany();
    // }

    // current group leader (from pre-registration table) 
    // public function currentGroupLeader()
    // {
    //     return $this->preRegistration->groupLeader;
    // }


    // all group leaders (history) 
    // public function groupLeaders(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         GroupLeader::class,
    //         PreRegistration::class,
    //         'pilgrim_id', // Foreign key on PreRegistration table...
    //         'id', // Foreign key on GroupLeader table...
    //         'id', // Local key on Pilgrim table...
    //         'group_leader_id' // Local key on PreRegistration table...
    //     );
    // }
}

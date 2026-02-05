<?php

namespace App\Models;

use App\Enums\CustomerRole;
use App\Models\Traits\HasAvatar;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasAvatar;
    use HasApiTokens;

    public $type = 'customer';

    protected $casts = [
        'role'              => CustomerRole::class,
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relations
    public function agency(): HasOne
    {
        return $this->hasOne(Agency::class);
    }

    public function customerAgency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'id');
    }

    // Helpers
    public function isOwner(): bool
    {
        return $this->role === CustomerRole::Customer;
    }

    public function isTeamMember(): bool
    {
        return $this->role === CustomerRole::TeamMember;
    }
}

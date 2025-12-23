<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as HasMustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        HasMustVerifyEmail,
        Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function borrow(): MorphOne // Raj Travels took money
    {
        return $this->morphOne(Loan::class, 'loanable')->where('direction', 'borrow');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function groupLeader(): HasOne
    {
        return $this->hasOne(GroupLeader::class);
    }

    public function lend(): MorphOne // Raj Travels gave money
    {
        return $this->morphOne(Loan::class, 'loanable')->where('direction', 'lend');
    }

    public function loans(): MorphMany
    {
        return $this->morphMany(Loan::class, 'loanable');
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . ($this->last_name ?? '');
    }
}

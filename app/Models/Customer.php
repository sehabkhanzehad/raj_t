<?php

namespace App\Models;

use App\Enums\CustomerRole;
use App\Models\Traits\HasAvatar;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

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
    public function agency(): Relation
    {
        return match (true) {
            $this->isOwner()      => $this->hasOne(Agency::class),
            $this->isTeamMember() => $this->belongsTo(Agency::class, 'agency_id', 'id'),
            default               => throw new \Exception("Invalid customer role for agency relation."),
        };
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

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  string  $yearId
     * @param  array  $abilities
     * @param  \DateTimeInterface|null  $expiresAt
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createToken(string $name, string $yearId, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null)
    {
        $plainTextToken = $this->generateTokenString();

        $token = $this->tokens()->create([
            'name' => $name,
            'year_id' => $yearId,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, "{$token->id}|$plainTextToken");
    }
}

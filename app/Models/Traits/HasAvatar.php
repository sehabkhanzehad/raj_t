<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasAvatar
{
    private const AVATAR_PREFIX = 'https://';

    private function hasAvatar(): bool
    {
        return $this->avatar && !$this->hasAvatarUrl();
    }

    private function hasAvatarUrl(?string $avatar = null): bool
    {
        $avatar ??= $this->getRawOriginal('avatar');
        return $avatar && str_contains($avatar, self::AVATAR_PREFIX);
    }

    public function deleteAvatar(?bool $save = false): void
    {
        if (!$this->hasAvatar()) return;

        Storage::delete($this->getRawOriginal('avatar'));

        if (!$save) return;

        $this->avatar = null;
        $this->save();
    }

    public function getAvatarAttribute(): ?string
    {
        $avatar = $this->attributes['avatar'];

        if (!$avatar) return null;

        if ($this->hasAvatarUrl($avatar)) return $avatar;

        return Storage::url($avatar);
    }
}

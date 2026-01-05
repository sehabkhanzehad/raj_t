<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Passport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Relations
    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function getFilePathAttribute(): ?string
    {
        $filePath = $this->attributes['file_path'];

        if (!$filePath) return null;

        return Storage::url($filePath);
    }

    public function deleteFile(): void
    {
        if (!$this->file_path) return;

        Storage::delete($this->getRawOriginal('file_path'));
    }
}

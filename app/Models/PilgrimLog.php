<?php

namespace App\Models;

use App\Enums\PilgrimLogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PilgrimLog extends Model
{
    protected $casts = [
        'type' => PilgrimLogType::class,
    ];

    protected $guarded = ['id'];

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public static function add(Pilgrim $pilgrim, string $refId, string $refType, PilgrimLogType $type, string $description, ?string $statusFrom = null, ?string $statusTo = null): void
    {
        self::create([
            'pilgrim_id'    => $pilgrim->id,
            'reference_id'  => $refId,
            'reference_type' => $refType,
            'type'          => $type,
            'description'   => $description,
            'status_from'   => $statusFrom,
            'status_to'     => $statusTo,
        ]);
    }
}

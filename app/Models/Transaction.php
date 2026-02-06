<?php

namespace App\Models;

use App\Enums\SectionType;
use App\Http\Requests\Api\TransactionRequest;
use App\Traits\HasYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Transaction extends Model
{
    use HasYear;
    // use HasReferences;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class);
    }

    public function addReferences(TransactionRequest $request): void
    {
        if (!$request->section()->needToAddReferences()) return;

        $config = $request->getReferenceConfig();

        if ($config['isArray']) {
            foreach ($request->{$config['key']} as $id) {
                $this->references()->create([
                    'referenceable_type' => $config['type'],
                    'referenceable_id' => $id,
                ]);
            }
        } elseif ($request->filled($config['key'])) {
            $this->references()->create([
                'referenceable_type' => $config['type'],
                'referenceable_id' => $request->{$config['key']},
            ]);
        }
    }
}

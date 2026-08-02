<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalContractItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'quantity' => 'decimal:3',
            'unit_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'initial_odometer' => 'decimal:2',
            'initial_hourmeter' => 'decimal:2',
            'final_odometer' => 'decimal:2',
            'final_hourmeter' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}

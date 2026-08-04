<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashMovement extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'posted',
        'reconciliation_status' => 'pending',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $movement): void {
            if (blank($movement->number) && filled($movement->organization_id)) {
                $movement->number = app(NumberSequenceService::class)->next(
                    organizationId: $movement->organization_id,
                    key: 'cash_movement',
                    name: 'Movimentações financeiras',
                    prefix: 'MOV-',
                    padding: 8,
                );
            }

            $movement->created_by ??= auth()->id();
        });
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'reconciled_at' => 'datetime',
            'value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(
            FinancialReceipt::class,
            'financial_receipt_id'
        );
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}

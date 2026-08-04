<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FinancialPayment extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'confirmed',
        'principal_value' => 0,
        'interest_value' => 0,
        'penalty_value' => 0,
        'discount_value' => 0,
        'additional_value' => 0,
        'total_paid' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            if (blank($payment->number) && filled($payment->organization_id)) {
                $payment->number = app(NumberSequenceService::class)->next(
                    organizationId: $payment->organization_id,
                    key: 'financial_payment',
                    name: 'Pagamentos financeiros',
                    prefix: 'PAG-',
                    padding: 8,
                );
            }

            $payment->created_by ??= auth()->id();
        });

        static::saving(function (self $payment): void {
            $payment->total_paid = max(
                0,
                (float) $payment->principal_value
                + (float) $payment->interest_value
                + (float) $payment->penalty_value
                + (float) $payment->additional_value
                - (float) $payment->discount_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'reversed_at' => 'datetime',
            'principal_value' => 'decimal:2',
            'interest_value' => 'decimal:2',
            'penalty_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_paid' => 'decimal:2',
        ];
    }

    public function payable(): BelongsTo
    {
        return $this->belongsTo(
            AccountPayable::class,
            'account_payable_id'
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            BusinessPartner::class,
            'supplier_id'
        );
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function cashMovements(): MorphMany
    {
        return $this->morphMany(
            CashMovement::class,
            'source'
        );
    }
}

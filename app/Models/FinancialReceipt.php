<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReceipt extends Model
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
        'total_received' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $receipt): void {
            if (blank($receipt->number) && filled($receipt->organization_id)) {
                $receipt->number = app(NumberSequenceService::class)->next(
                    organizationId: $receipt->organization_id,
                    key: 'financial_receipt',
                    name: 'Recebimentos financeiros',
                    prefix: 'REC-',
                    padding: 8,
                );
            }

            $receipt->created_by ??= auth()->id();
        });

        static::saving(function (self $receipt): void {
            $receipt->principal_value = (float) ($receipt->principal_value ?? 0);
            $receipt->interest_value = (float) ($receipt->interest_value ?? 0);
            $receipt->penalty_value = (float) ($receipt->penalty_value ?? 0);
            $receipt->discount_value = (float) ($receipt->discount_value ?? 0);
            $receipt->additional_value = (float) ($receipt->additional_value ?? 0);

            $receipt->total_received = max(
                0,
                $receipt->principal_value
                + $receipt->interest_value
                + $receipt->penalty_value
                + $receipt->additional_value
                - $receipt->discount_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'reversed_at' => 'datetime',
            'principal_value' => 'decimal:2',
            'interest_value' => 'decimal:2',
            'penalty_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_received' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(RentalInvoice::class, 'rental_invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'business_partner_id');
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}

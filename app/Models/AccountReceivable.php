<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountReceivable extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'accounts_receivable';

    protected $guarded = [];

    protected $attributes = [
        'status' => 'open',
        'installment_number' => 1,
        'installments_count' => 1,
        'original_value' => 0,
        'interest_value' => 0,
        'penalty_value' => 0,
        'discount_value' => 0,
        'paid_value' => 0,
        'open_value' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $receivable): void {
            if (blank($receivable->number) && filled($receivable->organization_id)) {
                $receivable->number = app(NumberSequenceService::class)->next(
                    organizationId: $receivable->organization_id,
                    key: 'account_receivable',
                    name: 'Contas a receber',
                    prefix: 'CR-',
                    padding: 8,
                );
            }
        });

        static::saving(function (self $receivable): void {
            $receivable->original_value = (float) ($receivable->original_value ?? 0);
            $receivable->interest_value = (float) ($receivable->interest_value ?? 0);
            $receivable->penalty_value = (float) ($receivable->penalty_value ?? 0);
            $receivable->discount_value = (float) ($receivable->discount_value ?? 0);
            $receivable->paid_value = (float) ($receivable->paid_value ?? 0);

            $gross = max(
                0,
                $receivable->original_value
                + $receivable->interest_value
                + $receivable->penalty_value
                - $receivable->discount_value
            );

            $receivable->open_value = max(0, $gross - $receivable->paid_value);

            if ($receivable->status !== 'cancelled') {
                $receivable->status = match (true) {
                    $gross > 0 && $receivable->open_value <= 0 => 'paid',
                    $receivable->paid_value > 0 => 'partially_paid',
                    $receivable->due_at?->isPast() => 'overdue',
                    default => 'open',
                };
            }
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
            'original_value' => 'decimal:2',
            'interest_value' => 'decimal:2',
            'penalty_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'paid_value' => 'decimal:2',
            'open_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(RentalInvoice::class, 'rental_invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'business_partner_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FinancialReceipt::class, 'account_receivable_id');
    }
}

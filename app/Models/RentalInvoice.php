<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalInvoice extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'draft',
        'subtotal' => 0,
        'discount_value' => 0,
        'additional_value' => 0,
        'total_value' => 0,
        'received_value' => 0,
        'open_value' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invoice): void {
            if (blank($invoice->number) && filled($invoice->organization_id)) {
                $invoice->number = app(NumberSequenceService::class)->next(
                    organizationId: $invoice->organization_id,
                    key: 'rental_invoice',
                    name: 'Faturas de locação',
                    prefix: 'FAT-',
                    padding: 7,
                );
            }
        });

        static::saving(function (self $invoice): void {
            $invoice->subtotal = (float) ($invoice->subtotal ?? 0);
            $invoice->discount_value = (float) ($invoice->discount_value ?? 0);
            $invoice->additional_value = (float) ($invoice->additional_value ?? 0);
            $invoice->received_value = (float) ($invoice->received_value ?? 0);

            $invoice->total_value = max(
                0,
                $invoice->subtotal
                - $invoice->discount_value
                + $invoice->additional_value
            );

            $invoice->open_value = max(
                0,
                $invoice->total_value - $invoice->received_value
            );

            if ($invoice->status !== 'cancelled') {
                $invoice->status = match (true) {
                    $invoice->total_value > 0 && $invoice->open_value <= 0 => 'paid',
                    $invoice->received_value > 0 => 'partially_paid',
                    $invoice->issued_at !== null => 'issued',
                    default => 'draft',
                };
            }
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'due_at' => 'date',
            'competence_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'received_value' => 'decimal:2',
            'open_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }

    public function rentalReturn(): BelongsTo
    {
        return $this->belongsTo(RentalReturn::class, 'return_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'business_partner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalInvoiceItem::class, 'invoice_id');
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(AccountReceivable::class, 'rental_invoice_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FinancialReceipt::class, 'rental_invoice_id');
    }
}

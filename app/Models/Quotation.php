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

class Quotation extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'draft',
        'estimated_total' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $quotation): void {
            $quotation->organization_id ??= auth()->user()?->organization_id;
            $quotation->responsible_user_id ??= auth()->id();
            $quotation->issued_at ??= today();

            if (blank($quotation->number) && filled($quotation->organization_id)) {
                $quotation->number = app(NumberSequenceService::class)->next(
                    organizationId: $quotation->organization_id,
                    key: 'quotation',
                    name: 'Cotações',
                    prefix: 'COT-',
                    padding: 8,
                );
            }
        });

        static::saved(function (self $quotation): void {
            $estimated = (float) $quotation->items()
                ->join('products', 'products.id', '=', 'quotation_items.product_id')
                ->sum(\DB::raw('quotation_items.quantity * products.target_cost'));

            if ((float) $quotation->estimated_total !== $estimated) {
                $quotation->updateQuietly(['estimated_total' => $estimated]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'response_deadline' => 'date',
            'closed_at' => 'datetime',
            'estimated_total' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class); }
    public function responsibleUser(): BelongsTo { return $this->belongsTo(User::class, 'responsible_user_id'); }
    public function suppliers(): HasMany { return $this->hasMany(QuotationSupplier::class); }
    public function items(): HasMany { return $this->hasMany(QuotationItem::class); }
}

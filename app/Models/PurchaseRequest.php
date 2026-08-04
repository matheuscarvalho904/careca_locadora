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

class PurchaseRequest extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'priority' => 'normal',
        'status' => 'draft',
        'estimated_total' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            $request->organization_id ??= auth()->user()?->organization_id;
            $request->requester_id ??= auth()->id();
            $request->requested_at ??= today();

            if (blank($request->number) && filled($request->organization_id)) {
                $request->number = app(NumberSequenceService::class)->next(
                    organizationId: $request->organization_id,
                    key: 'purchase_request',
                    name: 'Solicitações de compra',
                    prefix: 'SC-',
                    padding: 8,
                );
            }
        });

        static::saved(function (self $request): void {
            $total = (float) $request->items()->sum('estimated_total');

            if ((float) $request->estimated_total !== $total) {
                $request->updateQuietly(['estimated_total' => $total]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'requested_at' => 'date',
            'needed_at' => 'date',
            'estimated_total' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(CostCenter::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseRequestItem::class); }
}

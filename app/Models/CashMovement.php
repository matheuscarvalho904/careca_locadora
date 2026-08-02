<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'posted',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
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
        return $this->belongsTo(FinancialReceipt::class, 'financial_receipt_id');
    }
}

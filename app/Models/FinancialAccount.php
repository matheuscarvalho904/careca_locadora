<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialAccount extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'type' => 'bank',
        'opening_balance' => 0,
        'is_default' => false,
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FinancialReceipt::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function getCurrentBalanceAttribute(): float
    {
        $entries = (float) $this->movements()
            ->where('status', 'posted')
            ->where('type', 'entry')
            ->sum('value');

        $exits = (float) $this->movements()
            ->where('status', 'posted')
            ->where('type', 'exit')
            ->sum('value');

        return (float) $this->opening_balance + $entries - $exits;
    }
}

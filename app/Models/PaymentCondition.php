<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentCondition extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'installments' => 1,
        'first_due_days' => 0,
        'interval_days' => 30,
        'requires_down_payment' => false,
        'down_payment_percent' => 0,
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'requires_down_payment' => 'boolean',
            'down_payment_percent' => 'decimal:4',
        ];
    }
}

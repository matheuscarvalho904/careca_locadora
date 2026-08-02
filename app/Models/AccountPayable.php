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

class AccountPayable extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids, SoftDeletes;
    protected $table = 'accounts_payable';
    protected $guarded = [];
    protected $attributes = ['status'=>'draft','original_value'=>0,'interest_value'=>0,'penalty_value'=>0,'discount_value'=>0,'paid_value'=>0,'open_value'=>0];

    protected static function booted(): void
    {
        static::creating(function (self $payable): void {
            if (blank($payable->number) && filled($payable->organization_id)) {
                $payable->number = app(NumberSequenceService::class)->next(
                    organizationId: $payable->organization_id,
                    key: 'account_payable',
                    name: 'Contas a pagar',
                    prefix: 'CP-',
                    padding: 8,
                );
            }
        });

        static::saving(function (self $payable): void {
            $gross = max(0, (float)$payable->original_value + (float)$payable->interest_value + (float)$payable->penalty_value - (float)$payable->discount_value);
            $payable->open_value = max(0, $gross - (float)$payable->paid_value);
            if (! in_array($payable->status, ['draft','awaiting_approval','rejected','cancelled'], true)) {
                $payable->status = match (true) {
                    $gross > 0 && $payable->open_value <= 0 => 'paid',
                    (float)$payable->paid_value > 0 => 'partially_paid',
                    $payable->due_at?->isPast() => 'overdue',
                    default => 'approved',
                };
            }
        });
    }

    protected function casts(): array
    {
        return ['issued_at'=>'date','competence_date'=>'date','due_at'=>'date','approved_at'=>'datetime','original_value'=>'decimal:2','interest_value'=>'decimal:2','penalty_value'=>'decimal:2','discount_value'=>'decimal:2','paid_value'=>'decimal:2','open_value'=>'decimal:2','metadata'=>'array'];
    }

    public function supplier(): BelongsTo { return $this->belongsTo(BusinessPartner::class, 'supplier_id'); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function payments(): HasMany { return $this->hasMany(FinancialPayment::class); }
}

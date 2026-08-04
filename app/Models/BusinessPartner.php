<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPartner extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $partner): void {
            if (blank($partner->code) && filled($partner->organization_id)) {
                $partner->code = app(NumberSequenceService::class)->next(
                    organizationId: $partner->organization_id,
                    key: 'business_partner',
                    name: 'Parceiros',
                    prefix: 'PAR-',
                    padding: 6,
                );
            }
        });
    }

    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'tags' => 'array',
            'external_data' => 'array',
            'metadata' => 'array',
            'opened_at' => 'date',
            'credit_limit' => 'decimal:2',
            'payment_term_days' => 'integer',
            'internal_score' => 'integer',
            'credit_blocked' => 'boolean',
            'last_rental_at' => 'datetime',
            'last_contact_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'external_data_synced_at' => 'datetime',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BusinessPartnerContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(BusinessPartnerAddress::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BusinessPartnerDocument::class);
    }

    public function authorizedContacts(): HasMany
    {
        return $this->contacts()->where(function ($query): void {
            $query->where('can_withdraw_assets', true)
                ->orWhere('can_return_assets', true)
                ->orWhere('can_sign_contracts', true);
        });
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }

    public function isCustomer(): bool
    {
        return in_array('customer', $this->roles ?? [], true);
    }

    public function isCreditBlocked(): bool
    {
        return $this->credit_blocked || $this->status === 'blocked';
    }
    public function bankAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(\App\Models\BankAccount::class); }
}

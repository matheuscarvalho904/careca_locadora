<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'metadata' => 'array',
            'tags' => 'array',
            'external_data' => 'array',
            'opened_at' => 'date',
            'trial_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'activated_at' => 'datetime',
            'blocked_at' => 'datetime',
            'external_data_synced_at' => 'datetime',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function costCenters(): HasMany
    {
        return $this->hasMany(CostCenter::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

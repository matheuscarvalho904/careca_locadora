<?php

namespace App\Services\Numbering;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NumberSequenceService
{
    public function next(
        string $organizationId,
        string $key,
        string $name,
        string $prefix = '',
        int $padding = 6,
    ): string {
        return DB::transaction(function () use (
            $organizationId,
            $key,
            $name,
            $prefix,
            $padding
        ): string {
            NumberSequence::query()->firstOrCreate(
                [
                    'organization_id' => $organizationId,
                    'company_id' => null,
                    'branch_id' => null,
                    'key' => $key,
                ],
                [
                    'name' => $name,
                    'prefix' => $prefix,
                    'current_number' => 0,
                    'increment_by' => 1,
                    'padding' => $padding,
                    'format' => '{prefix}{number}',
                    'status' => 'active',
                ],
            );

            $sequence = NumberSequence::query()
                ->where('organization_id', $organizationId)
                ->whereNull('company_id')
                ->whereNull('branch_id')
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if ($sequence === null || $sequence->is_locked) {
                throw new RuntimeException('A sequência numérica está indisponível.');
            }

            $sequence->current_number += $sequence->increment_by;
            $sequence->save();

            $number = str_pad(
                (string) $sequence->current_number,
                $sequence->padding,
                '0',
                STR_PAD_LEFT,
            );

            return str_replace(
                ['{prefix}', '{number}', '{suffix}'],
                [$sequence->prefix ?? '', $number, $sequence->suffix ?? ''],
                $sequence->format,
            );
        });
    }
}

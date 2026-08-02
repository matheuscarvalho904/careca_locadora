<?php

namespace App\Services\Fleet;

use App\Data\Fleet\AssetClassificationResult;
use App\Models\AssetClassificationLog;
use App\Models\AssetClassificationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class AssetClassificationService
{
    /**
     * @param array<string, mixed> $assetData
     */
    public function classify(
        string $organizationId,
        array $assetData,
        ?string $assetId = null,
    ): AssetClassificationResult {
        $rules = AssetClassificationRule::query()
            ->withoutOrganizationScope()
            ->with('category:id,name')
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('priority')
            ->get();

        $best = new AssetClassificationResult();

        foreach ($rules as $rule) {
            [$confidence, $matchedFields] = $this->score($rule, $assetData);

            if (
                $confidence < $rule->minimum_confidence
                || $confidence <= $best->confidence
            ) {
                continue;
            }

            $best = new AssetClassificationResult(
                categoryId: $rule->category_id,
                categoryName: $rule->category?->name,
                ruleId: $rule->id,
                confidence: $confidence,
                meterType: $rule->meter_type,
                autoApply: $rule->auto_apply,
                matchedFields: $matchedFields,
            );
        }

        AssetClassificationLog::query()
            ->withoutOrganizationScope()
            ->create([
                'organization_id' => $organizationId,
                'asset_id' => $assetId,
                'rule_id' => $best->ruleId,
                'suggested_category_id' => $best->categoryId,
                'user_id' => Auth::id(),
                'plate' => $assetData['plate'] ?? null,
                'confidence' => $best->confidence,
                'auto_applied' => $best->canApplyAutomatically(),
                'matched_fields' => $best->matchedFields,
                'source_data' => $this->loggableData($assetData),
                'classified_at' => now(),
            ]);

        return $best;
    }

    /**
     * @param array<string, mixed> $assetData
     * @return array{0:int,1:array<int,string>}
     */
    private function score(
        AssetClassificationRule $rule,
        array $assetData,
    ): array {
        $score = 0;
        $matched = [];

        $checks = [
            'brand_pattern' => ['brand', 30],
            'model_pattern' => ['model', 30],
            'vehicle_type_pattern' => ['vehicle_type', 15],
            'body_type_pattern' => ['body_type', 15],
            'segment_pattern' => ['segment', 10],
            'subsegment_pattern' => ['subsegment', 10],
        ];

        foreach ($checks as $ruleField => [$dataField, $weight]) {
            $pattern = $rule->{$ruleField};

            if (blank($pattern)) {
                continue;
            }

            $value = $this->dataValue($assetData, $dataField);

            if ($this->matches($value, $pattern)) {
                $score += $weight;
                $matched[] = $dataField;
            }
        }

        $keywords = array_filter($rule->keywords ?? []);

        if ($keywords !== []) {
            $haystack = $this->searchableText($assetData);
            $hits = 0;

            foreach ($keywords as $keyword) {
                if ($this->matches($haystack, (string) $keyword)) {
                    $hits++;
                }
            }

            if ($hits > 0) {
                $score += min(20, $hits * 5);
                $matched[] = 'keywords';
            }
        }

        return [min(100, $score), array_values(array_unique($matched))];
    }

    private function matches(?string $value, string $pattern): bool
    {
        if (blank($value) || blank($pattern)) {
            return false;
        }

        return Str::contains(
            Str::upper(Str::ascii($value)),
            Str::upper(Str::ascii($pattern)),
        );
    }

    /**
     * @param array<string, mixed> $assetData
     */
    private function dataValue(array $assetData, string $field): ?string
    {
        $value = match ($field) {
            'vehicle_type' => data_get($assetData, 'external_data.vehicle_lookup.vehicle_type'),
            'body_type' => data_get($assetData, 'external_data.vehicle_lookup.body_type'),
            default => $assetData[$field] ?? null,
        };

        return is_scalar($value) ? trim((string) $value) : null;
    }

    /**
     * @param array<string, mixed> $assetData
     */
    private function searchableText(array $assetData): string
    {
        return collect([
            $this->dataValue($assetData, 'brand'),
            $this->dataValue($assetData, 'model'),
            $this->dataValue($assetData, 'vehicle_type'),
            $this->dataValue($assetData, 'body_type'),
            $this->dataValue($assetData, 'segment'),
            $this->dataValue($assetData, 'subsegment'),
            $this->dataValue($assetData, 'species'),
        ])->filter()->implode(' ');
    }

    /**
     * @param array<string, mixed> $assetData
     * @return array<string, mixed>
     */
    private function loggableData(array $assetData): array
    {
        return collect($assetData)
            ->except(['external_data'])
            ->all();
    }
}

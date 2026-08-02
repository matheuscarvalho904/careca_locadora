<?php

namespace App\Data\Fleet;

final readonly class AssetClassificationResult
{
    /**
     * @param array<int, string> $matchedFields
     */
    public function __construct(
        public ?string $categoryId = null,
        public ?string $categoryName = null,
        public ?string $ruleId = null,
        public int $confidence = 0,
        public string $meterType = 'odometer',
        public bool $autoApply = false,
        public array $matchedFields = [],
    ) {
    }

    public function matched(): bool
    {
        return filled($this->categoryId);
    }

    public function canApplyAutomatically(): bool
    {
        return $this->matched()
            && $this->autoApply
            && $this->confidence >= 85;
    }
}

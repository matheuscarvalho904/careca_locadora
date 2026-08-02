<?php

namespace App\Support\UI;

use Filament\Schemas\Components\Section;

final class PremiumSection
{
    /**
     * Cria uma seção cadastral em largura total com o padrão visual do ERP.
     *
     * @param array<int, mixed> $schema
     * @param array<string, int>|null $columns
     */
    public static function make(
        string $title,
        ?string $description,
        array $schema,
        ?array $columns = null,
        bool $collapsible = false,
    ): Section {
        $section = Section::make($title)
            ->description($description)
            ->columnSpanFull()
            ->columns($columns ?? PremiumFormLayout::standard())
            ->schema($schema)
            ->extraAttributes([
                'class' => 'careca-premium-section',
            ]);

        if ($collapsible) {
            $section->collapsible();
        }

        return $section;
    }

    private function __construct()
    {
    }
}

<?php

$file = dirname(__DIR__) . '/app/Filament/Resources/RentalReservations/Schemas/RentalReservationForm.php';
$content = file_get_contents($file);

$content = str_replace('LocaÃ§Ãµes', 'Locações', $content);
$content = str_replace('locaÃ§Ã£o', 'locação', $content);
$content = str_replace('DevoluÃ§Ã£o', 'Devolução', $content);
$content = str_replace('Retirada', 'Retirada', $content);

if (! str_contains($content, 'availableAssetOptions(')) {
    if (! str_contains($content, 'use App\Services\Rentals\RentalAvailabilityService;')) {
        $content = str_replace(
            'namespace App\Filament\Resources\RentalReservations\Schemas;',
            "namespace App\\Filament\\Resources\\RentalReservations\\Schemas;\n\nuse App\\Services\\Rentals\\RentalAvailabilityService;",
            $content
        );
    }

    $pattern = "/Select::make\\('asset_id'\\)(.*?)(?=\\n\\s*(?:TextInput|Select|DateTimePicker|DatePicker|Textarea|Toggle|Hidden)::make\\()/s";

    $replacement = <<<'PHP'
Select::make('asset_id')
                                    ->label('Ativo disponível')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->options(function (callable $get, $record): array {
                                        $startsAt = $get('../../pickup_expected_at');
                                        $endsAt = $get('../../return_expected_at');

                                        if (blank($startsAt) || blank($endsAt)) {
                                            return [];
                                        }

                                        return app(RentalAvailabilityService::class)
                                            ->availableAssetOptions(
                                                organizationId: (string) (auth()->user()?->organization_id),
                                                startsAt: $startsAt,
                                                endsAt: $endsAt,
                                                ignoreReservationId: $record?->reservation_id,
                                            );
                                    })
                                    ->getSearchResultsUsing(function (string $search, callable $get, $record): array {
                                        $startsAt = $get('../../pickup_expected_at');
                                        $endsAt = $get('../../return_expected_at');

                                        if (blank($startsAt) || blank($endsAt)) {
                                            return [];
                                        }

                                        return app(RentalAvailabilityService::class)
                                            ->availableAssetOptions(
                                                organizationId: (string) (auth()->user()?->organization_id),
                                                startsAt: $startsAt,
                                                endsAt: $endsAt,
                                                ignoreReservationId: $record?->reservation_id,
                                                search: $search,
                                            );
                                    }),

PHP;

    $new = preg_replace($pattern, $replacement, $content, 1, $count);

    if ($count === 0) {
        fwrite(STDERR, "Campo asset_id não encontrado no formulário de reservas.\n");
        exit(1);
    }

    $content = $new;
}

file_put_contents($file, $content);

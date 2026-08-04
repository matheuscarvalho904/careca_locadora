<?php

$file = dirname(__DIR__) . '/app/Filament/Resources/PurchaseOrders/PurchaseOrderResource.php';
$content = file_get_contents($file);

$replacements = [
    'ServiÃ§os' => 'Serviços',
    'IdentificaÃ§Ã£o' => 'Identificação',
    'NÃºmero' => 'Número',
    'CotaÃ§Ã£o' => 'Cotação',
    'RegularizaÃ§Ã£o' => 'Regularização',
    'EmissÃ£o' => 'Emissão',
    'PrevisÃ£o' => 'Previsão',
    'aprovaÃ§Ã£o' => 'aprovação',
    'aplicaÃ§Ãµes' => 'aplicações',
    'AplicaÃ§Ã£o' => 'Aplicação',
    'aplicaÃ§Ã£o' => 'aplicação',
    'HodÃ´metro' => 'Hodômetro',
    'HorÃ­metro' => 'Horímetro',
    'unitÃ¡rio' => 'unitário',
    'ObservaÃ§Ãµes' => 'Observações',
    'CondiÃ§Ã£o' => 'Condição',
    'TransferÃªncia' => 'Transferência',
    'CartÃ£o' => 'Cartão',
    'crÃ©dito' => 'crédito',
    'dÃ©bito' => 'débito',
    'bancÃ¡ria' => 'bancária',
    'ComunicaÃ§Ã£o' => 'Comunicação',
    'â€”' => '—',
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

if (! str_contains($content, 'use Filament\Schemas\Components\Utilities\Get;')) {
    $content = str_replace(
        'use Filament\Schemas\Components\Tabs\Tab;',
        "use Filament\\Schemas\\Components\\Tabs\\Tab;\nuse Filament\\Schemas\\Components\\Utilities\\Get;",
        $content
    );
}

$content = str_replace('fn ($get): bool =>', 'fn (Get $get): bool =>', $content);
$content = str_replace('fn ($get): ?string =>', 'fn (Get $get): ?string =>', $content);

file_put_contents($file, $content);

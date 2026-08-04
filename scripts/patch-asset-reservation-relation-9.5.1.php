<?php

$file = dirname(__DIR__) . '/app/Models/Asset.php';
$content = file_get_contents($file);

if (! str_contains($content, 'function rentalReservationItems()')) {
    $method = <<<'PHP'

    public function rentalReservationItems(): HasMany
    {
        return $this->hasMany(RentalReservationItem::class);
    }
PHP;
    $pos = strrpos($content, "\n}");
    $content = substr_replace($content, $method . "\n}", $pos, 2);
}

file_put_contents($file, $content);

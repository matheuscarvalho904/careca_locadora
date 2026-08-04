<?php

$file = dirname(__DIR__) . '/app/Models/RentalReservation.php';
$content = file_get_contents($file);

$old = <<<'PHP'
        return in_array($this->status, [
            'pending',
            'confirmed',
            'preparing',
            'converted',
        ], true);
PHP;

$new = <<<'PHP'
        return in_array(
            $this->status,
            \App\Services\Rentals\RentalAvailabilityService::BLOCKING_STATUSES,
            true,
        );
PHP;

$content = str_replace($old, $new, $content);
$content = str_replace('Reservas de locaÃ§Ã£o', 'Reservas de locação', $content);
file_put_contents($file, $content);

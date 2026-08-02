<?php

namespace App\Support\UI;

final class PremiumFormLayout
{
    /**
     * Grid padrão para seções cadastrais.
     *
     * @return array<string, int>
     */
    public static function standard(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    /**
     * Grid para seções com campos menores, como fiscal e endereço.
     *
     * @return array<string, int>
     */
    public static function dense(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
            '2xl' => 6,
        ];
    }

    /**
     * Grid para repeaters de contatos e endereços.
     *
     * @return array<string, int>
     */
    public static function repeater(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    private function __construct()
    {
    }
}

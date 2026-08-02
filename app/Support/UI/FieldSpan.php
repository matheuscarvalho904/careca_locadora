<?php

namespace App\Support\UI;

final class FieldSpan
{
    /**
     * @return array<string, int>
     */
    public static function full(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function half(): array
    {
        return [
            'default' => 1,
            'md' => 2,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function threeQuarters(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    private function __construct()
    {
    }
}

<?php

namespace App\Support\UI;

use Filament\Support\RawJs;

final class BrazilInputMask
{
    public static function cpfCnpj(): RawJs
    {
        return RawJs::make(<<<'JS'
            $input.replace(/\D/g, '').length <= 11
                ? '999.999.999-99'
                : '99.999.999/9999-99'
        JS);
    }

    public static function phone(): RawJs
    {
        return RawJs::make(<<<'JS'
            $input.replace(/\D/g, '').length <= 10
                ? '(99) 9999-9999'
                : '(99) 99999-9999'
        JS);
    }

    public static function postalCode(): string
    {
        return '99.999-999';
    }

    public static function plate(): RawJs
    {
        return RawJs::make(<<<'JS'
            $input.toUpperCase().replace(/[^A-Z0-9]/g, '').length <= 7
                ? 'aaa9a99'
                : 'aaa9a99'
        JS);
    }

    /**
     * @return array<int, string>
     */
    public static function documentStripCharacters(): array
    {
        return ['.', '/', '-'];
    }

    /**
     * @return array<int, string>
     */
    public static function phoneStripCharacters(): array
    {
        return ['(', ')', ' ', '-'];
    }

    /**
     * @return array<int, string>
     */
    public static function postalCodeStripCharacters(): array
    {
        return ['.', '-'];
    }

    private function __construct()
    {
    }
}

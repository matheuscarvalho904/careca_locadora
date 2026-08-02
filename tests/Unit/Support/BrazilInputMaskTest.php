<?php

namespace Tests\Unit\Support;

use App\Support\UI\BrazilInputMask;
use Filament\Support\RawJs;
use PHPUnit\Framework\TestCase;

class BrazilInputMaskTest extends TestCase
{
    public function test_document_mask_is_dynamic_for_cpf_and_cnpj(): void
    {
        $this->assertInstanceOf(RawJs::class, BrazilInputMask::cpfCnpj());
        $this->assertSame(['.', '/', '-'], BrazilInputMask::documentStripCharacters());
    }

    public function test_phone_and_postal_code_masks_strip_formatting_before_save(): void
    {
        $this->assertInstanceOf(RawJs::class, BrazilInputMask::phone());
        $this->assertSame('99.999-999', BrazilInputMask::postalCode());
        $this->assertSame(
            ['(', ')', ' ', '-'],
            BrazilInputMask::phoneStripCharacters()
        );
        $this->assertSame(
            ['.', '-'],
            BrazilInputMask::postalCodeStripCharacters()
        );
    }
}

<?php

namespace Tests\Unit\Support;

use App\Support\UI\FieldSpan;
use App\Support\UI\PremiumFormLayout;
use App\Support\UI\StatusPalette;
use PHPUnit\Framework\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_status_palette_translates_operational_statuses(): void
    {
        $this->assertSame('success', StatusPalette::color('available'));
        $this->assertSame('Disponível', StatusPalette::label('available'));
        $this->assertSame('danger', StatusPalette::color('maintenance'));
        $this->assertSame('Em manutenção', StatusPalette::label('maintenance'));
    }

    public function test_field_spans_avoid_overcrowded_forms(): void
    {
        $this->assertSame(4, PremiumFormLayout::standard()['xl']);
        $this->assertSame(2, FieldSpan::half()['md']);
        $this->assertSame(3, FieldSpan::threeQuarters()['xl']);
    }
}

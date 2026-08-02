<?php

namespace Tests\Unit\Support;

use App\Support\UI\PremiumFormLayout;
use PHPUnit\Framework\TestCase;

class PremiumFormLayoutTest extends TestCase
{
    public function test_standard_grid_is_responsive_and_not_overcrowded(): void
    {
        $this->assertSame([
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ], PremiumFormLayout::standard());
    }

    public function test_repeater_grid_uses_at_most_four_columns(): void
    {
        $this->assertSame(4, PremiumFormLayout::repeater()['xl']);
    }
}

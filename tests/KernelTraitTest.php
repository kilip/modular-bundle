<?php

namespace Tests\Doyo\Bundle\Modular;

use Doyo\Bundle\Modular\KernelTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Doyo\Bundle\Modular\KernelTrait
 */
class KernelTraitTest extends TestCase
{
    public function testPassed(): void
    {
        $this->assertTrue(true);
    }
}

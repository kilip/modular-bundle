<?php

/*
 * This file is part of the Doyo Modular Bundle project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Doyo\Bundle\Modular;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Doyo\Bundle\Modular\KernelTrait
 */
class KernelTraitTest extends TestCase
{
    public function test_passed(): void
    {
        $this->assertTrue(true);
    }
}

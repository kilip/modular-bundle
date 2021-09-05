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

namespace spec\Doyo\Bundle\Modular;

use Doyo\Bundle\Modular\KernelTrait;
use PhpSpec\ObjectBehavior;

class KernelTraitSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(KernelTrait::class);
    }
}

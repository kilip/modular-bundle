<?php

/*
 * This file is part of the ModularBundle project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Doyo\Bundle\Modular\Routing;

use Doyo\Bundle\Modular\Routing\ModuleLoader;
use PhpSpec\ObjectBehavior;

class ModuleLoaderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ModuleLoader::class);
    }
}

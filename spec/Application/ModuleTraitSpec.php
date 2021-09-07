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

namespace spec\Doyo\Bundle\Modular\Application;

use App\Test\TestModule;
use PhpSpec\ObjectBehavior;

/**
 * @covers \Doyo\Bundle\Modular\Application\ModuleTrait
 */
class ModuleTraitSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf(TestModule::class);
        $this->boot();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TestModule::class);
    }

    public function it_should_define_module_name()
    {
        $this->getName()->shouldReturn('test');
    }

    public function it_should_define_module_path()
    {
        $r = new \ReflectionClass(TestModule::class);
        $this->getBasePath()->shouldReturn(\dirname($r->getFileName()));
    }

    public function it_should_define_module_namespace()
    {
        $this->getNamespace()->shouldReturn('App\\Test');
    }
}

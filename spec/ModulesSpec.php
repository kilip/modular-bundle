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

namespace spec\Doyo\Bundle\Modular;

use Doyo\Bundle\Modular\Modules;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ModulesSpec extends ObjectBehavior
{
    public function let(
        ContainerBuilder $container,
        Definition $kernelDefinition
    ) {
        $kernelDefinition->getClass()
            ->shouldBeCalled()->willReturn('App\\Kernel');
        $container->getDefinition('kernel')
            ->shouldBeCalled()->willReturn($kernelDefinition);
        $container->setParameter('app.test.base_path', Argument::type('string'))
            ->shouldBeCalled();
        $container->setParameter('app.mongo_db.base_path', Argument::type('string'))
            ->shouldBeCalled();
        $this->buildModules($container);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Modules::class);
    }

    public function it_should_store_available_modules()
    {
        $this->getModules()->shouldBeArray();
        $this->getModules()->shouldHaveCount(2);
    }
}

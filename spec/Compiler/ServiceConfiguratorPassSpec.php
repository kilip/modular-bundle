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

namespace spec\Doyo\Bundle\Modular\Compiler;

use App\Kernel;
use App\Test\TestModule;
use Doyo\Bundle\Modular\Compiler\ServiceConfiguratorPass;
use Doyo\Bundle\Modular\Modules;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ServiceConfiguratorPassSpec extends ObjectBehavior
{
    public function let()
    {
        $module = new TestModule();
        $module->boot();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ServiceConfiguratorPass::class);
    }

    public function it_should_implement_compiler_pass_interface()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    public function it_should_load_configuration_from_module_resource_directory(
        ContainerBuilder $container,
        Definition $kernelDefinition,
        Modules $modules
    ) {
        $container->getDefinition('kernel')
            ->shouldBeCalled()->willReturn($kernelDefinition);
        $kernelDefinition->getClass()
            ->shouldBeCalled()->willReturn(Kernel::class);
        $container->getReflectionClass(Kernel::class, true)
            ->shouldBeCalled()->willReturn($r = new ReflectionClass(Kernel::class));
        $container->getParameter('kernel.environment')->willReturn('test');
        $container->getParameter('kernel.project_dir')
            ->willReturn(\dirname($r->getFileName()));
        $container->getParameter('kernel.cache_dir')
            ->willReturn(sys_get_temp_dir().'/doyo-modular/cache');
        $container->fileExists(Argument::any())->willReturn(true);
        $container->get('doyo.modules')
            ->willReturn($modules);

        $module = new TestModule();
        $module->boot();
        $modules->getModules()
            ->willReturn(['test' => $module]);

        $container->setParameter(Argument::cetera())
            ->shouldBeCalled();
        $container->removeBindings(Argument::cetera())
            ->shouldBeCalled();
        $container->setDefinition(Argument::cetera())
            ->shouldBeCalled();

        $this->process($container);
    }
}

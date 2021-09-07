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

namespace Doyo\Bundle\Modular\Compiler;

use Doyo\Bundle\Modular\Application\ModuleInterface;
use Doyo\Bundle\Modular\Modules;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ServiceConfiguratorPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $modules = new Modules();
        $modules->buildModules($container);
        foreach ($modules->getModules() as $module) {
            $this->configureService($container, $module);
        }
    }

    /**
     * @throws ReflectionException
     * @psalm-suppress PossiblyInvalidCast
     */
    private function configureService(ContainerBuilder $container, ModuleInterface $module): void
    {
        $env          = (string) $container->getParameter('kernel.environment');
        $configurator = $this->createConfigurator($container);
        $paths        = ['config', 'services'];

        foreach ($paths as $path) {
            if (is_dir($dir=$module->getBasePath().'/Resources/'.$path)) {
                $configurator->import($dir.'/*.yaml');
                $configurator->import($dir.'/*.xml');
                $configurator->import($dir.'/{'.$env.'}/*.yaml');
                $configurator->import($dir.'/{'.$env.'}/*.xml');
            }
        }
    }

    /**
     * @throws ReflectionException
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     * @psalm-suppress PossiblyInvalidCast
     * @psalm-suppress UndefinedThisPropertyFetch
     * @psalm-suppress MissingClosureReturnType
     * @psalm-suppress PossiblyInvalidFunctionCall
     */
    private function createConfigurator(ContainerBuilder $container): ContainerConfigurator
    {
        $projectDir = (string) $container->getParameter('kernel.project_dir');
        $env        = (string) $container->getParameter('kernel.environment');
        $locator    = new FileLocator($projectDir);
        $resolver   = new LoaderResolver([
            new XmlFileLoader($container, $locator, $env),
            new YamlFileLoader($container, $locator, $env),
            new PhpFileLoader($container, $locator, $env),
            new DirectoryLoader($container, $locator, $env),
        ]);

        $class  = $container->getDefinition('kernel')->getClass();
        /** @var ReflectionClass $r */
        $r      = $container->getReflectionClass($class, true);
        $file   = $r->getFileName();
        $loader = new DelegatingLoader($resolver);
        /** @var PhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file);
        $instanceof   = &\Closure::bind(function &() { return $this->instanceof; }, $kernelLoader, $kernelLoader)();

        return new ContainerConfigurator($container, $kernelLoader, $instanceof, $file, $file, $env);
    }
}

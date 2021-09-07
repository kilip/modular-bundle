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
use Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ServiceConfiguratorPass implements CompilerPassInterface
{
    /**
     * @psalm-suppress PossiblyInvalidCast
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $env              = (string) $container->getParameter('kernel.environment');
        $projectDir       = (string) $container->getParameter('kernel.project_dir');
        $locator          = new FileLocator($projectDir);
        $cacheDir         = (string) $container->getParameter('kernel.cache_dir');
        $builderGenerator = class_exists(ConfigBuilderGenerator::class) ?
            new ConfigBuilderGenerator($cacheDir)
            : null;
        $resolver   = new LoaderResolver([
            new XmlFileLoader($container, $locator, $env),
            new YamlFileLoader($container, $locator, $env),
            new PhpFileLoader(
                $container,
                $locator,
                $env,
                $builderGenerator
            ),
            new GlobFileLoader($container, $locator, $env),
            new DirectoryLoader($container, $locator, $env),
            new ClosureLoader($container, $env),
        ]);
        $loader = new DelegatingLoader($resolver);

        $loader->load(function (ContainerBuilder $container) use ($loader) {
            /** @var Modules $modules */
            $modules = $container->get('doyo.modules');
            $configurator = $this->createConfigurator($container, $loader);
            foreach ($modules->getModules() as $module) {
                $this->configureService($container, $configurator, $module);
            }
        });
    }

    /**
     * @psalm-suppress PossiblyInvalidCast
     * @psalm-suppress MissingClosureReturnType
     * @psalm-suppress PossiblyNullOperand
     */
    private function configureService(ContainerBuilder $container, ContainerConfigurator $configurator, ModuleInterface $module): void
    {
        $env          = $container->getParameter('kernel.environment');
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
     * @psalm-suppress PossiblyInvalidFunctionCall
     * @psalm-suppress MissingClosureReturnType
     */
    private function createConfigurator(ContainerBuilder $container, LoaderInterface $loader): ContainerConfigurator
    {
        $env        = (string) $container->getParameter('kernel.environment');
        $class      = $container->getDefinition('kernel')->getClass();
        /** @var ReflectionClass $r */
        $r      = $container->getReflectionClass($class, true);
        $file   = $r->getFileName();

        /** @var PhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file);
        $kernelLoader->setCurrentDir(\dirname($file));
        $instanceof   = &\Closure::bind(function &() { return $this->instanceof; }, $kernelLoader, $kernelLoader)();

        return new ContainerConfigurator($container, $kernelLoader, $instanceof, $file, $file, $env);
    }
}

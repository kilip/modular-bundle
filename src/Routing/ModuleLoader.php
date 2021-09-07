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

namespace Doyo\Bundle\Modular\Routing;

use Doyo\Bundle\Modular\Application\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\ContainerLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutingPhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

class ModuleLoader extends Loader
{
    private bool $loaded = false;
    private KernelInterface $kernel;

    /**
     * @var iterable<array-key,ModuleInterface>|ModuleInterface[]
     */
    private iterable $modules;
    private ContainerLoader $loader;

    /**
     * @param iterable<array-key,ModuleInterface> $modules
     */
    public function __construct(
        string $env,
        KernelInterface $kernel,
        ContainerLoader $loader,
        iterable $modules
    ) {
        parent::__construct($env);

        $this->kernel  = $kernel;
        $this->loader  = $loader;
        $this->modules = $modules;
    }

    /**
     * @psalm-suppress PossiblyFalseArgument
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress UndefinedInterfaceMethod
     * @psalm-suppress PossiblyFalseReference
     */
    public function load($resource, string $type = null): RouteCollection
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $loader = $this->loader;
        $file   = (new \ReflectionObject($this->kernel))->getFileName();
        /** @psalm-var RoutingPhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file, 'php');
        \assert($kernelLoader instanceof RoutingPhpFileLoader);
        $kernelLoader->setCurrentDir(\dirname($file));
        $collection   = new RouteCollection();
        $configurator = new RoutingConfigurator($collection, $kernelLoader, $file, $file, $this->env);

        foreach ($this->modules as $module) {
            $this->loadRoute($configurator, $module);
        }

        return $collection;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'modular' === $type;
    }

    private function loadRoute(RoutingConfigurator $configurator, ModuleInterface $module): void
    {
        $routePath = $module->getBasePath().'/Resources/routes';
        if (is_dir($routePath)) {
            $configurator->import($routePath.'/*.yaml');
            $configurator->import($routePath.'/*.xml');
        }
    }
}

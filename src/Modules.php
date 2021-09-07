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

namespace Doyo\Bundle\Modular;

use Doctrine\Inflector\InflectorFactory;
use Doyo\Bundle\Modular\Application\ModuleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class Modules
{
    /**
     * @var array<array-key,ModuleInterface>
     */
    private static array $modules = [];

    /**
     * @return ModuleInterface[]
     */
    public function getModules(): array
    {
        return static::$modules;
    }

    public function buildModules(ContainerBuilder $container): void
    {
        $this->initModules($container);
    }

    public function initModules(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('kernel');
        /** @var class-string<KernelInterface> $kernelClass */
        $kernelClass = $definition->getClass();
        $r           = new \ReflectionClass($kernelClass);
        $dir         = \dirname((string) $r->getFileName());

        $finder = Finder::create()
            ->in($dir)
            ->depth(1)
            ->name('*Module.php');

        /** @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            /** @var class-string<ModuleInterface> $class */
            $class = $r->getNamespaceName().'\\'.$file->getRelativePath().'\\'.$file->getBasename('.php');
            if (class_exists($class, true)) {
                $this->registerModule($container, $class);
            }
        }
    }

    /**
     * @param class-string<ModuleInterface> $class
     */
    private function registerModule(ContainerBuilder $container, string $class): void
    {
        $module = new $class();
        $module->boot();
        $inflector = InflectorFactory::create()->build();
        $exp       = explode('\\', $module->getNamespace());
        $paramNS   = $inflector->tableize($exp[0].'.'.$module->getName());
        $container->setParameter($paramNS.'.base_path', $module->getBasePath());
        static::$modules[$module->getName()] = $module;
    }
}

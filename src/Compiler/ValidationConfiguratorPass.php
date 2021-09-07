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
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class ValidationConfiguratorPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('validator.builder');
        /** @var Modules $modules */
        $modules = $container->get('doyo.modules');

        foreach ($modules->getModules() as $module) {
            $this->configureValidation($container, $module);
        }
    }

    /**
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress MixedArrayAssignment
     * @psalm-suppress MixedAssignment
     * @psalm-suppress UnusedClosureParam
     * @psalm-suppress MixedClosureParamType
     * @psalm-suppress MissingClosureParamType
     * @psalm-suppress MixedArgument
     * @noinspection PhpArrayUsedOnlyForWriteInspection
     */
    private function configureValidation(ContainerBuilder $container, ModuleInterface $module): void
    {
        $files        = [];
        $fileRecorder = function ($extension, $path) use (&$files): void {
            $files['xml'][] = $path;
        };

        $path = $module->getBasePath().'/Resources/validation';

        if (is_dir($path)) {
            $container->addResource(new DirectoryResource($path));
            $this->registerMappingFilesFromDir($path, $fileRecorder);
        }

        $validatorBuilder = $container->getDefinition('validator.builder');
        if (\array_key_exists('xml', $files)) {
            $validatorBuilder->addMethodCall('addXmlMappings', [$files['xml']]);
        }
    }

    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress MixedAssignment
     */
    private function registerMappingFilesFromDir(string $dir, \Closure $fileRecorder): void
    {
        foreach (Finder::create()->followLinks()->files()->in($dir)->name('/\.(xml|ya?ml)$/')->sortByName() as $file) {
            $fileRecorder($file->getExtension(), $file->getRealPath());
        }
    }
}

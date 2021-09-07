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

namespace Doyo\Bundle\Modular\DependencyInjection;

use Doyo\Bundle\Modular\Application\ModuleInterface;
use Doyo\Bundle\Modular\Modules;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class ModularExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs      = $container->getExtensionConfig($this->getAlias());
        $resolvingBag = $container->getParameterBag();
        $configs      = $resolvingBag->resolveValue($configs);
        /** @var array<array-key,array<array-key,string|bool|scalar>> $configs */
        $configs       = $this->processConfiguration(new Configuration(), $configs);
        $modules       = new Modules();
        $modules->buildModules($container);
        $container->set('doyo.modules', $modules);
        foreach ($modules->getModules() as $module) {
            $this->configureModule($configs, $container, $module);
        }
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->configureParams($container, $config);
    }

    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress PossiblyInvalidArrayOffset
     * @psalm-suppress PossiblyInvalidArgument
     * @psalm-param array<array-key,string|array<array-key,string>> $config
     */
    private function configureParams(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('doyo.modular.module_dir', $config['module_root_dir']);
        $container->setParameter('doyo.modular.paths.api_platform', $config['config_paths']['api_platform']);
        $container->setParameter('doyo.modular.paths.validation', $config['config_paths']['validation']);
        $container->setParameter('doyo.modular.paths.serialization', $config['config_paths']['serialization']);

        $this->configureDoctrineParams($container, $config['doctrine']);
    }

    /**
     * @param array<array-key,string> $config
     */
    private function configureDoctrineParams(ContainerBuilder $container, array $config): void
    {
        $ns = 'doyo.modular.doctrine';

        foreach ($config as $key => $val) {
            $container->setParameter($ns.'.'.$key, $val);
        }
    }

    /**
     * @param array<array-key,array<array-key,string|bool|scalar>> $configs
     */
    private function configureModule(array $configs, ContainerBuilder $container, ModuleInterface $module): void
    {
        if ($container->hasExtension('doctrine')) {
            $this->prependSerialization($configs, $container, $module);
            $this->prependOrm($configs, $container, $module);
        }
    }

    /**
     * @param array<array-key,array<array-key,string|bool|scalar>> $configs
     * @psalm-suppress PossiblyInvalidCast
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     */
    private function prependOrm(array $configs, ContainerBuilder $container, ModuleInterface $module): void
    {
        $basePath      = $module->getBasePath();
        $config        = $configs['doctrine'];
        $useAnnotation = $config['use_annotation'];
        $nsSuffix      = $config['entity_dir'];
        $entityDir     = $basePath.\DIRECTORY_SEPARATOR.$nsSuffix;
        $mappingType   = $config['mapping_type'];
        $mappingDir    = $basePath.\DIRECTORY_SEPARATOR.$config['mapping_dir'];

        if (true === $useAnnotation && ! is_dir($entityDir)) {
            return;
        }

        if (false === $useAnnotation && ! is_dir($mappingDir)) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    $module->getName() => [
                        'is_bundle' => false,
                        'type' => $useAnnotation ? 'annotation' : $mappingType,
                        'dir' => $entityDir,
                        'prefix' => $module->getNamespace().'\\'.$nsSuffix,
                        'alias' => $module->getName(),
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array<array-key,array<array-key,string|bool|scalar>> $configs
     */
    private function prependSerialization(array $configs, ContainerBuilder $container, ModuleInterface $module): void
    {
        //$configPath = (string) $container->getParameter('doyo.modular.paths.serialization');
        $configPath = $configs['config_paths']['serialization'];
        $path       = $module->getBasePath().'/'.$configPath;
        if (is_dir($path)) {
            $container->prependExtensionConfig('framework', [
                'serializer' => [
                    'mapping' => [
                        'paths' => [
                            $path,
                        ],
                    ],
                ],
            ]);
        }
    }
}

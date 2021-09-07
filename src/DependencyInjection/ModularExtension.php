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
        $container->setParameter('doyo.modular.use_annotation', $config['use_annotation']);
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
        $this->prependSerialization($configs, $container, $module);
        if ($container->hasExtension('doctrine')) {
            $this->prependOrm($configs, $container, $module);
        }

        if ($container->hasExtension('doctrine_mongodb')) {
            $this->prependMongoDB($configs, $container, $module);
        }

        if ($container->hasExtension('api_platform')) {
            $this->prependApiPlatform($configs, $container, $module);
        }
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
        $useAnnotation = $configs['use_annotation'];
        $nsSuffix      = $config['entity_dir'];
        $entityDir     = $basePath.\DIRECTORY_SEPARATOR.$nsSuffix;
        $mappingDir    = $basePath.\DIRECTORY_SEPARATOR.$config['mapping_dir'];
        $mappingNS     = $module->getNamespace().'\\'.$nsSuffix;

        if ( ! is_dir($entityDir)) {
            return;
        }

        $extensionConfig =  [
            'orm' => [
                'mappings' => [
                    $module->getName() => [
                        'is_bundle' => false,
                        'type' => $useAnnotation ? 'annotation' : 'xml',
                        'dir' => $useAnnotation ? $entityDir : $mappingDir,
                        'prefix' => $mappingNS,
                        'alias' => $module->getName(),
                    ],
                ],
            ],
        ];

        if (\count($resolved = $module->getResolveTargetEntities()) > 0) {
            $extensionConfig['orm']['resolve_target_entities'] = $resolved;
        }

        $container->prependExtensionConfig('doctrine', $extensionConfig);
    }

    /**
     * @param array<array-key,array<array-key,string|bool|scalar>> $configs
     * @psalm-suppress PossiblyInvalidCast
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     */
    private function prependMongoDB(array $configs, ContainerBuilder $container, ModuleInterface $module): void
    {
        $basePath        = $module->getBasePath();
        $config          = $configs['doctrine'];
        $useAnnotation   = $configs['use_annotation'];
        $nsSuffix        = $config['document_dir'];
        $documentDir     = $basePath.\DIRECTORY_SEPARATOR.$nsSuffix;
        $mappingDir      = $basePath.\DIRECTORY_SEPARATOR.$config['mapping_dir'];
        $mappingNS       = $module->getNamespace().'\\'.$nsSuffix;

        if ( ! is_dir($documentDir)) {
            return;
        }
        $extensionConfig = [
            'document_managers' => [
                'default' => [
                    'mappings' => [
                        $module->getName() => [
                            'is_bundle' => false,
                            'type' => $useAnnotation ? 'annotation' : 'xml',
                            'dir' => $useAnnotation ? $documentDir : $mappingDir,
                            'prefix' => $mappingNS,
                            'alias' => $module->getName(),
                        ],
                    ],
                ],
            ],
        ];

        if (\count($resolved = $module->getResolveTargetEntities()) > 0) {
            $extensionConfig['resolve_target_documents'] = $resolved;
        }
        $container->prependExtensionConfig('doctrine_mongodb', $extensionConfig);
    }

    /**
     * @param array<array-key,array<array-key,string|bool|scalar>> $configs
     */
    private function prependApiPlatform(array $configs, ContainerBuilder $container, ModuleInterface $module): void
    {
        $useAnnotation = $configs['use_annotation'];
        $basePath      = $module->getBasePath();
        $apiConfig     = $basePath.\DIRECTORY_SEPARATOR.$configs['config_paths']['api_platform'];
        $doctrine      = $configs['doctrine'];
        $modelSuffix   = $doctrine['use_orm'] ? $doctrine['entity_dir'] : $doctrine['document_dir'];
        $modelDir      = $basePath.\DIRECTORY_SEPARATOR.$modelSuffix;
        $dir           = $useAnnotation ? $modelDir : $apiConfig;

        if (is_dir($dir)) {
            $container->prependExtensionConfig('api_platform', [
                'mapping' => [
                    'paths' => [$dir],
                ],
            ]);
        }
    }
}

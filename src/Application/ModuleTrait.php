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

namespace Doyo\Bundle\Modular\Application;

use Doctrine\Inflector\InflectorFactory;
use ReflectionClass;

trait ModuleTrait
{
    protected string $name;
    protected string $basePath;
    protected string $namespace;

    public function boot(): void
    {
        $inflector = InflectorFactory::create()->build();
        $r         = new ReflectionClass(__CLASS__);

        $className                           = str_replace('Module', '', $r->getShortName());
        $this->name                          = $inflector->tableize($className);
        $this->basePath                      = \dirname($r->getFileName());
        $this->namespace                     = $r->getNamespaceName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return array<array-key,string|class-string>
     */
    public function getResolveTargetEntities(): array
    {
        return [];
    }
}

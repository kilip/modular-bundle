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

interface ModuleInterface
{
    public function boot(): void;

    public function getName(): string;

    public function getBasePath(): string;

    public function getNamespace(): string;
}

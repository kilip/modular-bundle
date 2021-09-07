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

namespace App\MongoDB;

use App\MongoDB\Document\Person;
use App\Test\Contracts\PersonInterface;
use Doyo\Bundle\Modular\Application\ModuleInterface;
use Doyo\Bundle\Modular\Application\ModuleTrait;

class MongoDBModule implements ModuleInterface
{
    use ModuleTrait;

    public function getName(): string
    {
        return 'mongo_db';
    }

    public function getResolveTargetEntities(): array
    {
        return [
            PersonInterface::class => Person::class,
        ];
    }
}

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

$bundles =  [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true],
    Doyo\Bundle\Modular\ModularBundle::class => ['all' => true],
];

if ('mongodb' === $this->getEnvironment()) {
    $bundles[Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle::class] = ['all' => true];
} else {
    $bundles[Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class] = ['all' => true];
}

return $bundles;

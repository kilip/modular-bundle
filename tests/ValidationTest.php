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

namespace Tests\Doyo\Bundle\Modular;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

/**
 * @covers \Doyo\Bundle\Modular\DependencyInjection\Configuration
 * @covers \Doyo\Bundle\Modular\DependencyInjection\DoyoModularExtension
 * @covers \Doyo\Bundle\Modular\DoyoModularBundle
 * @covers \Doyo\Bundle\Modular\Modules
 * @covers \Doyo\Bundle\Modular\Application\ModuleTrait
 * @covers \Doyo\Bundle\Modular\Compiler\ServiceConfiguratorPass
 * @covers \Doyo\Bundle\Modular\Routing\ModuleLoader
 */
class ValidationTest extends ApiTestCase
{
    public function test_validation(): void
    {
        $client = static::createClient(['debug' => false]);
        $client->request('POST', '/people', [
            'json' => [
            ],
        ]);

        $this->assertJsonContains(['hydra:description' => 'name: This value should not be blank.']);
    }
}

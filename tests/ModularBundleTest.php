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
use App\Test\Contracts\PersonInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Router;

/**
 * @covers \Doyo\Bundle\Modular\DependencyInjection\Configuration
 * @covers \Doyo\Bundle\Modular\DependencyInjection\DoyoModularExtension
 * @covers \Doyo\Bundle\Modular\DoyoModularBundle
 * @covers \Doyo\Bundle\Modular\Modules
 * @covers \Doyo\Bundle\Modular\Application\ModuleTrait
 * @covers \Doyo\Bundle\Modular\Compiler\ServiceConfiguratorPass
 * @covers \Doyo\Bundle\Modular\Routing\ModuleLoader
 * @covers \Doyo\Bundle\Modular\Compiler\ValidationConfiguratorPass
 */
class ModularBundleTest extends ApiTestCase
{
    public function test_config_loaded(): void
    {
        static::bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->hasParameter('app.test.base_path'));
        $this->assertTrue($container->hasParameter('app.mongo_db.base_path'));
        $this->assertTrue($container->hasParameter('foo.config'));
        $this->assertTrue($container->hasParameter('foo.service'));

        $this->assertTrue($container->has('test.controllers.test'), 'test controller should be loaded');
        $this->assertTrue($container->has('test.foo_service'), 'foo service should be loaded');
    }

    public function test_routes_loaded(): void
    {
        static::bootKernel();

        $container = $this->getContainer();
        /** @var Router $router */
        $router = $container->get('router');
        $this->assertNotNull($router->getRouteCollection()->get('test_route'));
    }

    /**
     * @dataProvider getModelLoadedTestData
     */
    public function test_doctrine_loaded(string $class, string $env = 'test'): void
    {
        static::bootKernel(['environment' => $env]);
        $id = 'mongodb' === $env ? 'doctrine_mongodb' : 'doctrine';
        /** @var ManagerRegistry $registry */
        $registry   = $this->getContainer()->get($id);
        $manager    = $registry->getManager();
        $repository = $manager->getRepository($class);

        $this->assertNotNull($repository);
    }

    public function getModelLoadedTestData(): array
    {
        return [
            [PersonInterface::class],
            [PersonInterface::class, 'mongodb'],
        ];
    }

    public function test_api_platform(): void
    {
        static::bootKernel();
        $container = $this->getContainer();

        /** @var Router $router */
        $router = $container->get('router');
        $this->assertNotNull($router->getRouteCollection()->get('api_people_get_collection'));
        $this->assertNotNull($router->getRouteCollection()->get('api_customers_get_collection'));
    }

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

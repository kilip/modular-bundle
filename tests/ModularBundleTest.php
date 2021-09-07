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

use App\Test\Entity\Person;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \Doyo\Bundle\Modular\DependencyInjection\Configuration
 * @covers \Doyo\Bundle\Modular\DependencyInjection\ModularExtension
 * @covers \Doyo\Bundle\Modular\ModularBundle
 * @covers \Doyo\Bundle\Modular\Modules
 * @covers \Doyo\Bundle\Modular\Application\ModuleTrait
 * @covers \Doyo\Bundle\Modular\Compiler\ServiceConfiguratorPass
 */
class ModularBundleTest extends KernelTestCase
{
    public function test_loaded_bundle(): void
    {
        $kernel    = static::bootKernel();
        $container = $kernel->getContainer();
        $this->assertArrayHasKey('ModularBundle', $kernel->getBundles());

        $this->assertTrue($container->hasParameter('app.test.base_path'));
        $this->assertTrue($container->hasParameter('foo.config'));
        $this->assertTrue($container->hasParameter('foo.service'));
    }

    public function test_config_loaded()
    {
        static::bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->hasParameter('doyo.modular.doctrine.mapping_type'));
    }

    /**
     * @dataProvider getModelLoadedTestData
     */
    public function test_doctrine_loaded(string $class)
    {
        static::bootKernel();
        /** @var ObjectManager $manager */
        $manager = $this->getContainer()->get('doctrine')
            ->getManager();
        $repository = $manager->getRepository($class);
        $this->assertNotNull($repository);
    }

    public function getModelLoadedTestData()
    {
        return [
            [Person::class],
        ];
    }
}

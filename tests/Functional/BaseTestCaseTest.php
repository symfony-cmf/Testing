<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Functional;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\Bundle\PHPCRBundle\Test\RepositoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Cmf\Component\Testing\Functional\DbManager\PHPCR;
use Symfony\Cmf\Component\Testing\Tests\Fixtures\TestTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseTestCaseTest extends TestCase
{
    private Container&MockObject $container;
    private KernelInterface&MockObject $kernel;
    private TestTestCase $testCase;
    private KernelBrowser&MockObject $client;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistryInterface::class);
        $initializerManager = $this->createMock(InitializerManager::class);
        $this->container = $this->createMock(Container::class);
        $this->container
            ->method('get')
            ->willReturnCallback(function ($name) use ($managerRegistry, $initializerManager) {
                $dic = [
                    'test.client' => $this->client,
                    'test.service_container' => $this->container,
                    'doctrine_phpcr' => $managerRegistry,
                    'doctrine_phpcr.initializer_manager' => $initializerManager,
                ];

                return $dic[$name];
            });

        $this->kernel = $this->createMock(KernelInterface::class);
        $this->kernel
            ->method('getContainer')
            ->willReturn($this->container)
        ;
        $this->kernel
            ->method('getEnvironment')
            ->willReturn('phpcr')
        ;

        $this->testCase = new TestTestCase();
        $this->testCase->setKernel($this->kernel);

        $this->client = $this->createMock(KernelBrowser::class);

        $this->client
            ->method('getContainer')
            ->willReturn($this->container);
    }

    public function testGetKernel(): void
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getKernel');

        $this->assertInstanceOf(KernelInterface::class, $method->invoke(null));
    }

    public function testItCanProvideAFrameworkBundleClient(): void
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getFrameworkBundleClient');

        $this->assertInstanceOf(KernelBrowser::class, $method->invoke($this->testCase));
    }

    public function provideTestDb(): array
    {
        return [
            ['PHPCR', 'PHPCR'],
            ['Phpcr', 'PHPCR'],
            ['ORM', 'ORM'],
            ['foobar', null],
        ];
    }

    /**
     * @dataProvider provideTestDb
     */
    public function testDb(string $dbName, string|null $expected): void
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getDbManager');

        if (null === $expected) {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage($dbName.'" does not exist');
        }

        $res = $method->invoke($this->testCase, $dbName);
        if (null === $expected) {
            // do not do assertions if the expected exception has not been thrown.
            return;
        }

        $className = \sprintf(
            'Symfony\Cmf\Component\Testing\Functional\DbManager\%s',
            $expected
        );
        if (PHPCR::class === $className && class_exists(RepositoryManager::class)) {
            $className = RepositoryManager::class;
        }

        $this->assertInstanceOf($className, $res);
    }
}

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
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Cmf\Component\Testing\Functional\DbManager\PHPCR;
use Symfony\Cmf\Component\Testing\Tests\Fixtures\TestTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseTestCaseTest extends TestCase
{
    /**
     * @var Container&MockObject
     */
    private $container;

    /**
     * @var KernelInterface&MockObject
     */
    private $kernel;

    /**
     * @var TestTestCase
     */
    private $testCase;

    /**
     * @var KernelBrowser|Client|MockObject
     */
    private $client;

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

        if (class_exists(KernelBrowser::class)) {
            $this->client = $this->createMock(KernelBrowser::class);
        } else {
            $this->client = $this->createMock(Client::class);
        }

        $this->client->expects($this->any())
            ->method('getContainer')
            ->willReturn($this->container);
    }

    public function testGetContainer()
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getContainer');
        $method->setAccessible(true);

        $this->assertEquals($this->container, $method->invoke(null));
    }

    public function testGetKernel()
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getKernel');
        $method->setAccessible(true);

        $this->assertInstanceOf(KernelInterface::class, $method->invoke(null));
    }

    public function testItCanProvideAFrameworkBundleClient()
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getFrameworkBundleClient');
        $method->setAccessible(true);

        if (class_exists(KernelBrowser::class)) {
            $this->assertInstanceOf(KernelBrowser::class, $method->invoke($this->testCase));
        } else {
            $this->assertInstanceOf(Client::class, $method->invoke($this->testCase));
        }
    }

    public function provideTestDb()
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
     *
     * @depends testGetContainer
     */
    public function testDb($dbName, $expected)
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getDbManager');
        $method->setAccessible(true);

        if (null === $expected) {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage($dbName.'" does not exist');
        }

        $res = $method->invoke($this->testCase, $dbName);
        if (null === $expected) {
            // do not do assertions if the expected exception has not been thrown.
            return;
        }

        $className = sprintf(
            'Symfony\Cmf\Component\Testing\Functional\DbManager\%s',
            $expected
        );
        if (PHPCR::class === $className && class_exists(RepositoryManager::class)) {
            $className = RepositoryManager::class;
        }

        $this->assertInstanceOf($className, $res);
    }
}

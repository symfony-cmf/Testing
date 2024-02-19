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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
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
     * @var KernelBrowser&MockObject
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

        $this->client = $this->createMock(KernelBrowser::class);
        $this->client
            ->method('getContainer')
            ->willReturn($this->container);
    }

    public function testGetKernel(): void
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getKernel');
        $method->setAccessible(true);

        $this->assertInstanceOf(KernelInterface::class, $method->invoke(null));
    }

    public function testItCanProvideAFrameworkBundleClient(): void
    {
        $class = new \ReflectionClass(BaseTestCase::class);
        $method = $class->getMethod('getFrameworkBundleClient');
        $method->setAccessible(true);

        $this->assertInstanceOf(KernelBrowser::class, $method->invoke($this->testCase));
    }
}

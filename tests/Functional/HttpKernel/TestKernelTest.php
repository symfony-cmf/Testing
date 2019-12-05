<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\HttpKernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class TestKernelTest extends TestCase
{
    /**
     * @var TestKernel
     */
    private $kernel;

    private $mockBundle;

    protected function setUp(): void
    {
        $this->kernel = $this->getMockBuilder(TestKernel::class)
            ->setConstructorArgs(['test', true])
            ->getMockForAbstractClass();

        $this->mockBundle = $this->createMock(BundleInterface::class);
    }

    /**
     * @dataProvider bundleSetProvider
     */
    public function testBundleSetRequire(array $bundleSets, array $expectedBundles)
    {
        $this->kernel->requireBundleSets($bundleSets);
        $bundles = array_keys($this->kernel->registerBundles());

        foreach ($expectedBundles as $key => $value) {
            $this->assertArrayHasKey($key, $bundles);
            $this->assertSame($value, $bundles[$key]);
        }
    }

    public function bundleSetProvider()
    {
        return [
            [['default'], [FrameworkBundle::class, SecurityBundle::class, TwigBundle::class]],
            [['phpcr_odm'], [DoctrineBundle::class, DoctrinePHPCRBundle::class]],
            [['doctrine_orm'], [DoctrineBundle::class]],
        ];
    }

    public function testBundleAdd()
    {
        $this->kernel->addBundle($this->mockBundle);
        $this->kernel->addBundle($this->mockBundle);

        $this->assertCount(2, $this->kernel->registerBundles());
    }

    public function testRequireInvalidBundleSet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->kernel->requireBundleSet('foobar');
    }
}

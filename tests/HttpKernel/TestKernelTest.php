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
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Kernel;

class TestKernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kernel
     */
    private $kernel;

    private $mockBundle;

    protected function setUp()
    {
        $this->kernel = $this->getMockBuilder('Symfony\Cmf\Component\Testing\HttpKernel\TestKernel')
            ->setConstructorArgs(['test', true])
            ->getMockForAbstractClass();

        $this->mockBundle = $this->createMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
    }

    /**
     * @dataProvider bundleSetProvider
     */
    public function testBundleSetRequire(array $bundleSets, array $expectedBundles)
    {
        $this->kernel->requireBundleSets($bundleSets);
        $bundles = array_keys($this->kernel->registerBundles());

        $this->assertArraySubset($expectedBundles, $bundles);
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRequireInvalidBundleSet()
    {
        $this->kernel->requireBundleSet('foobar');
    }
}

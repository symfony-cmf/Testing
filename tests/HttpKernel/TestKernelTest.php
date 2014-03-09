<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Tests\HttpKernel;

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class TestKernelTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->kernel = $this->getMockBuilder(
            'Symfony\Cmf\Component\Testing\HttpKernel\TestKernel'
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->mockBundle = $this->getMock(
            'Symfony\Component\HttpKernel\Bundle\BundleInterface'
        );
    }

    public function testBundleSetRequire()
    {
        $this->kernel->init();
        $this->kernel->requireBundleSets(array(
            'default', 'phpcr_odm'
        ));
        $bundles = $this->kernel->registerBundles();
        $this->assertCount(6, $bundles);
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
        $this->kernel->init();
        $this->kernel->requireBundleSet('foobar');
    }
}

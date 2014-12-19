<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Testing\Tests\Functional;

use Symfony\Cmf\Component\Testing\Tests\Fixtures\TestTestCase;
use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class BaseTestCaseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->testCase = new TestTestCase();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $me = $this;
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($me) {
                $dic = array(
                    'test.client' => $me->client,
                );

                return $dic[$name];
            }));

        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        $this->testCase->setKernel($this->kernel);

        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

        $this->client = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->client->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

    }

    public function testGetContainer()
    {
        $this->assertEquals($this->container, $this->testCase->getContainer());
    }

    public function provideTestDb()
    {
        return array(
            array('PHPCR', 'PHPCR'),
            array('Phpcr', 'PHPCR'),
            array('ORM', 'ORM'),
            array('foobar', null),
        );
    }

    /**
     * @dataProvider provideTestDb
     * @depends testGetContainer
     */
    public function testDb($dbName, $expected)
    {
        if (null === $expected) {
            $this->setExpectedException('InvalidArgumentException', $dbName.'" does not exist');
        }

        $res = $this->testCase->getDbManager($dbName);

        $className = sprintf(
            'Symfony\Cmf\Component\Testing\Functional\DbManager\%s',
            $expected
        );

        $this->assertInstanceOf($className, $res);
    }
}

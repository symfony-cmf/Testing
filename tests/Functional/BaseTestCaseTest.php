<?php

namespace Tests\Functional;

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class BaseTestCaseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $me = $this;

        $this->tc = $this->getMockBuilder(
            'Symfony\Cmf\Component\Testing\Functional\BaseTestCase'
        )->setMethods(array(
            'createKernel',
        ))->getMockForAbstractClass();

        $this->container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );

        $this->kernel = $this->getMock(
            'Symfony\Component\HttpKernel\KernelInterface'
        );

        $this->client = $me->getMockBuilder(
            'Symfony\Bundle\FrameworkBundle\Client'
        )->disableOriginalConstructor()->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($me) {
                $dic = array(
                    'test.client' => $me->client
                );

                return $dic[$name];
            }));

        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

        $tc = $this->tc;
        $tc::staticExpects($this->any())
            ->method('createKernel')
            ->will($this->returnValue($this->kernel));

        $this->client->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

    }

    public function testGetContainer()
    {
        $res = $this->tc->getContainer();
        $this->assertEquals($this->container, $res);
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
            $this->setExpectedException('InvalidArgumentException',
                $dbName.'" does not exist'
            );
        }

        $res = $this->tc->getDbManager($dbName);

        $className = sprintf(
            'Symfony\Cmf\Component\Testing\Functional\DbManager\%s',
            $expected
        );

        $this->assertInstanceOf($className, $res);
    }
}

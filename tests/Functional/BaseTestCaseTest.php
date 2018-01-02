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

use Symfony\Cmf\Component\Testing\Tests\Fixtures\TestTestCase;

class BaseTestCaseTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $kernel;
    private $testCase;
    private $client;

    protected function setUp()
    {
        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) {
                $dic = [
                    'test.client' => $this->client,
                ];

                return $dic[$name];
            }));

        $this->kernel = $this->createMock('Symfony\Component\HttpKernel\KernelInterface');
        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->willReturn($this->container);

        $this->testCase = new TestTestCase();
        $this->testCase->setKernel($this->kernel);

        $this->client = $this->createMock('Symfony\Bundle\FrameworkBundle\Client');
        $this->client->expects($this->any())
            ->method('getContainer')
            ->willReturn($this->container);
    }

    public function testGetContainer()
    {
        $this->assertEquals($this->container, $this->testCase->getContainer());
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

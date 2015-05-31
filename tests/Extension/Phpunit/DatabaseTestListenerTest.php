<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Extension\Phpunit;

use Symfony\Cmf\Component\Testing\Extension\Phpunit\DatabaseTestListener;

class DatabaseTestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DatabaseTestListener
     */
    protected $listener;
    protected $mockManager;

    public function setUp()
    {
        $this->listener = new DatabaseTestListener();
        $this->listener->addManagers(array('mock' => $this->getManager()));
    }

    public function testDatabaseIsCreatedOnceAndPurgedBeforeEachTest()
    {
        $testCase = $this->getTestCase();

        $this->getManager()->shouldReceive('setUpDatabase')->once();
        $this->getManager()->shouldReceive('purgeDatabase')->twice();

        $this->listener->startTest($testCase);
        $this->listener->startTest($testCase);
    }

    public function testDatabaseIsDeletedAfterTheTestSuite()
    {
        $suite = \Mockery::mock('PHPUnit_Framework_TestSuite');

        $this->getManager()->shouldReceive('setUpDatabase')->once();
        $this->getManager()->shouldReceive('purgeDatabase')->once();
        $this->getManager()->shouldReceive('dropDatabase')->once();

        $this->listener->startTest($this->getTestCase());
        $this->listener->startTestSuite($suite);
    }

    /**
     * @expectedException \Symfony\Cmf\Component\Testing\Exception\ManagerNotFoundException
     * @expectedExceptionMessage No database manager found for driver "mongodb".
     */
    public function testUnknownDatabaseDriver()
    {
        $testCase = $this->getTestCase('mongodb');

        $this->listener->startTest($testCase);
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function getTestCase($driver = 'mock')
    {
        $testCase = \Mockery::mock('PHPUnit_Framework_TestCase', 'Symfony\Cmf\Component\Testing\RequiresDatabaseInterface');
        $testCase->shouldReceive('getDatabaseDriverName')->andReturn($driver);
        $testCase->shouldReceive('setDbManager');

        return $testCase;
    }

    protected function getManager()
    {
        if (null === $this->mockManager) {
            $this->mockManager = \Mockery::mock('Symfony\Cmf\Component\Testing\Database\Manager\ManagerInterface');
            $this->mockManager->shouldReceive('getDriver')->andReturn('mock');
        }

        return $this->mockManager;
    }
}

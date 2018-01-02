<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Phpunit;

use Symfony\Cmf\Component\Testing\Phpunit\DatabaseTestListener;

class DatabaseTestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $listener;
    private $processBuilder;
    private static $i;

    protected function setUp()
    {
        $this->listener = new DatabaseTestListener($this->getProcessBuilder());
        self::$i = 0;
    }

    public function testPhpcrTestSuite()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('phpcr');

        $this->assertProcessExecuted(['doctrine:phpcr:init:dbal', '--drop', '--force']);
        $this->assertProcessExecuted(['doctrine:phpcr:repository:init']);

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertContains('[PHPCR]', ob_get_clean());
    }

    public function testFallsBackToOldInitDbalCommand()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('phpcr');

        $this->assertProcessExecuted(['doctrine:phpcr:init:dbal', '--drop', '--force'], false);
        $this->assertProcessExecuted(['doctrine:phpcr:init:dbal', '--drop'], true);
        $this->assertProcessExecuted(['doctrine:phpcr:repository:init']);

        ob_start();
        $this->listener->startTestSuite($suite);
        ob_end_clean();
    }

    public function testOrmTestSuite()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('orm');

        $this->assertProcessExecuted(['doctrine:schema:drop', '--env=orm', '--force']);
        $this->assertProcessExecuted(['doctrine:database:create', '--env=orm']);
        $this->assertProcessExecuted(['doctrine:schema:create', '--env=orm']);

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertContains('[ORM]', ob_get_clean());
    }

    public function testUnknownTestSuite()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('not orm or phpcr tests');

        $this->getProcessBuilder()->expects($this->never())->method('setArguments');

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertContains('[not orm or phpcr tests]', ob_get_clean());
    }

    protected function assertProcessExecuted(array $arguments, $successfull = true)
    {
        $process = $this->createMock('Symfony\Component\Process\Process');

        $process->expects($this->once())->method('run');

        $process->expects($this->any())
            ->method('isSuccessful')
            ->willReturn($successfull);

        $processPlaceholder = $this->getMockBuilder('ProcessPlaceholder')
            ->setMethods(['getProcess'])
            ->getMock();
        $processPlaceholder->expects($this->once())
            ->method('getProcess')
            ->willReturn($process);

        $this->getProcessBuilder()
            ->expects($this->at(self::$i++))
            ->method('setArguments')
            ->with($this->equalTo($arguments))
            ->willReturn($processPlaceholder);
    }

    protected function getProcessBuilder()
    {
        if (null === $this->processBuilder) {
            $this->processBuilder = $this->createMock('Symfony\Component\Process\ProcessBuilder');
        }

        return $this->processBuilder;
    }
}

<?php

namespace Tests\Phpunit;

use Symfony\Cmf\Component\Testing\Phpunit\DatabaseTestListener;

class DatabaseTestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $listener;
    private $processBuilder;
    private static $i;

    public function setUp()
    {
        $this->listener = new DatabaseTestListener($this->getProcessBuilder());
        self::$i = 0;
    }

    public function testPhpcrTestSuite()
    {
        $suite = $this->getMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('phpcr'));

        $this->assertProcessExecuted(array('doctrine:phpcr:init:dbal', '--drop'));
        $this->assertProcessExecuted(array('doctrine:phpcr:repository:init'));

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertEquals(PHP_EOL.PHP_EOL.'[PHPCR]'.PHP_EOL, ob_get_clean());
    }

    public function testOrmTestSuite()
    {
        $suite = $this->getMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('orm'));

        $this->assertProcessExecuted(array('doctrine:schema:drop', '--env=orm', '--force'));
        $this->assertProcessExecuted(array('doctrine:database:create', '--env=orm'));
        $this->assertProcessExecuted(array('doctrine:schema:create', '--env=orm'));

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertEquals(PHP_EOL.PHP_EOL.'[ORM]'.PHP_EOL, ob_get_clean());
    }

    public function testUnknownTestSuite()
    {
        $suite = $this->getMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('not orm or phpcr tests'));

        $this->getProcessBuilder()
            ->expects($this->never())
            ->method('setArguments');

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertEquals(PHP_EOL.PHP_EOL.'[not orm or phpcr tests]'.PHP_EOL, ob_get_clean());
    }

    protected function assertProcessExecuted(array $arguments, $successfull = true)
    {
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $process->expects($this->once())
            ->method('run');

        $process->expects($this->once())
            ->method('isTerminated')
            ->will($this->returnValue(true));

        $process->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue($successfull));

        $processPlaceholder = $this->getMock('ProcessPlaceholder', array('getProcess'));
        $processPlaceholder->expects($this->once())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $this->getProcessBuilder()
            ->expects($this->at(self::$i++))
            ->method('setArguments')
            ->with($this->equalTo($arguments))
            ->will($this->returnValue($processPlaceholder));
    }

    protected function getProcessBuilder()
    {
        if (null === $this->processBuilder) {
            $this->processBuilder = $this->getMock('Symfony\Component\Process\ProcessBuilder');
        }

        return $this->processBuilder;
    }
}

<?php

namespace Symfony\Cmf\Component\Testing\Tests\Database\Manager;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class BaseManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $processBuilder;
    protected $container;

    public function tearDown()
    {
        \Mockery::close();
    }

    protected function assertProcessExecuted(array $arguments, $successful = true)
    {
        $process = \Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('isSuccessful')->andReturn($successful)->ordered();

        $processBuilder = \Mockery::mock('Symfony\Component\Process\ProcessBuilder');
        $processBuilder->shouldReceive('getProcess')->andReturn($process);

        $this->getProcessBuilder()
            ->shouldReceive('setArguments')
            ->with($arguments)
            ->andReturn($processBuilder)
            ->once();
    }

    protected function getContainer()
    {
        if (null === $this->container) {
            $this->container = \Mockery::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        }

        return $this->container;
    }

    protected function getProcessBuilder()
    {
        if (null === $this->processBuilder) {
            $this->processBuilder = \Mockery::mock('Symfony\Component\Process\ProcessBuilder');
        }

        return $this->processBuilder;
    }
}

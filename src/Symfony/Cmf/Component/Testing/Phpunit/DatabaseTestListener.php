<?php

namespace Symfony\Cmf\Component\Testing\Phpunit;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\Kernel;
use Doctrine\Common\DataFixtures\Purger;

class DatabaseTestListener implements \PHPUnit_Framework_TestListener
{
    protected static $currentSuite;
    private $processBuilder;

    public function __construct($processBuilder = null)
    {
        if (null === $processBuilder) {
            $this->processBuilder = new PrefixedProcessBuilder();
            $this->processBuilder->setPrefix(array('php', __DIR__.'/../../../../../../bin/console'));
        } else {
            $this->processBuilder = $processBuilder;
        }
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
 
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }
 
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
 
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
 
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        switch (static::$currentSuite->getName()) {
            case 'orm':
                $db = $test->getDbManager('ORM');
                $purger = new Purger\ORMPurger($db->getOm());
                break;

            case 'phpcr':
                $db = $test->getDbManager('PHPCR');
                $purger = new Purger\PHPCRPurger($db->getOm());
                break;

            default;
                return;
        }

        $purger->purge();
    }
 
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
    }
 
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        static::$currentSuite = $suite;

        switch ($suite->getName()) {
            case 'orm':
                $this->setUpOrmDatabase($suite);
                break;

            case 'phpcr':
                $this->setUpPhpcrDatabase($suite);
                break;

            default:
                if (!class_exists($suite->getName())) {
                    echo PHP_EOL.PHP_EOL.'['.$suite->getName().']'.PHP_EOL;
                }
        }
    }

    private function setUpPhpcrDatabase($suite)
    {
        echo PHP_EOL.PHP_EOL;

        $process = $this->processBuilder
            ->setArguments(array('doctrine:phpcr:init:dbal', '--drop'))
            ->getProcess();
        $process->run();

        while (true) {
            if ($process->isTerminated()) {
                if (!$process->isSuccessful()) {
                    $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                    $suite->markTestSuiteSkipped('[PHPCR] Error when initializing dbal: '.$output);
                } else {
                    $process = $this->processBuilder
                        ->setArguments(array('doctrine:phpcr:repository:init'))
                        ->getProcess();
                    $process->run();

                    while (true) {
                        if ($process->isTerminated()) {
                            if (!$process->isSuccessful()) {
                                $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                                $suite->markTestSuiteSkipped('[PHPCR] Error when initializing repositories: '.$output);
                            } else {
                                echo '[PHPCR]'.PHP_EOL;
                            }
                        }

                        break;
                    }
                }

                break;
            }
        }
    }

    private function setUpOrmDatabase($suite)
    {
        echo PHP_EOL.PHP_EOL;

        $process = $this->processBuilder
            ->setArguments(array('doctrine:schema:drop', '--env=orm', '--force'))
            ->getProcess();
        $process->run();

        while (true) {
            if ($process->isTerminated()) {
                if (!$process->isSuccessful()) {
                    $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                    $suite->markTestSuiteSkipped('[ORM] Error when dropping database: '.$output);
                    return;
                }
                break;
            }
        }

        $process = $this->processBuilder
            ->setArguments(array('doctrine:database:create', '--env=orm'))
            ->getProcess();
        $process->run();

        while (true) {
            if ($process->isTerminated()) {
                if (!$process->isSuccessful()) {
                    $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                    $suite->markTestSuiteSkipped('[ORM] Error when creating database: '.$output);
                } else {
                    $process = $this->processBuilder
                        ->setArguments(array('doctrine:schema:create', '--env=orm'))
                        ->getProcess();
                    $process->run();

                    while (true) {
                        if ($process->isTerminated()) {
                            if (!$process->isSuccessful()) {
                                $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                                $suite->markTestSuiteSkipped('[ORM] Error when creating schema: '.$output);
                            } else {
                                echo '[ORM]'.PHP_EOL;
                            }
                        }

                        break;
                    }
                }

                break;
            }
        }
    }
 
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        if (!in_array($suite->getName(), array('phpcr', 'orm'))) {
            return;
        }

        $process = $this->processBuilder
            ->setArguments(array('doctrine:database:drop', '--force'))
            ->getProcess();
        $process->run();

        while (true) {
            if ($process->isTerminated()) {
                if (!$process->isSuccessful()) {
                    $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                    $suite->markTestSuiteSkipped('Error when dropping database: '.$output);
                }
            }
            break;
        }
    }
}

class PrefixedProcessBuilder extends ProcessBuilder
{
    private $prefixes = array();
    private $activated = true;

    public function __construct(array $arguments = array())
    {
        parent::__construct($arguments);

        if (class_exists('Symfony\Component\HttpKernel\Kernel')) {
            $this->activated = Kernel::VERSION_ID <= 20400;
        } else {
            $ref = new \ReflectionMethod(get_parent_class($this), 'setPrefix'); 
            $doc = $ref->getDocComment();

            $this->activated = false === strpos('array', $doc);
        }
    }

    public function setPrefix($prefix)
    {
        $prefixes = is_array($prefix) ? $prefix : array($prefix);

        if (!$this->activated) {
            return parent::setPrefix($prefix);
        }

        foreach ($prefixes as $prefix) {
            $this->prefixes[] = $prefix;
        }
        
        return $this;
    }

    public function getProcess()
    {
        if (!$this->activated || 0 === count($this->prefixes)) {
            return parent::getProcess();
        }

        $process = parent::getProcess();
        $command = implode(' ', array_map('escapeshellarg', $this->prefixes)).' '.$process->getCommandLine();

        $process->setCommandLine($command);

        return $process;
    }
}

<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Phpunit;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;
use Doctrine\Common\DataFixtures\Purger;

class DatabaseTestListener implements \PHPUnit_Framework_TestListener
{
    protected static $currentSuite;
    private $processBuilder;
    private $prefix = array();

    public function __construct($processBuilder = null)
    {
        if (null === $processBuilder) {
            $this->processBuilder = new ProcessBuilder();
            $phpExecutableFinder = new PhpExecutableFinder();
            $phpExecutable = $phpExecutableFinder->find(false);
            if (false === $phpExecutable) {
                throw new \RuntimeException('No PHP executable found on the current system.');
            }

            // Symfony 2.3 does not support array prefix, so we have to implement it ourselves
            $this->prefix = array($phpExecutable, __DIR__.'/../../bin/console');
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

    public function addWarning(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_Warning $e, $time)
    {
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
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

            default:

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
                if (!class_exists($suite->getName()) && false === strpos($suite->getName(), '::')) {
                    echo PHP_EOL.PHP_EOL.'['.$suite->getName().']'.PHP_EOL;
                }
        }
    }

    private function setUpPhpcrDatabase($suite)
    {
        echo PHP_EOL.PHP_EOL;

        // initialize PHPCR DBAL (new way)
        $process = $this->processBuilder
            ->setArguments(array_merge($this->prefix, array('doctrine:phpcr:init:dbal', '--drop', '--force')))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            // try initializing the old way (Jackalope <1.2)
            $process = $this->processBuilder
                ->setArguments(array_merge($this->prefix, array('doctrine:phpcr:init:dbal', '--drop')))
                ->getProcess();

            $process->run();

            if (!$process->isSuccessful()) {
                $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                $suite->markTestSuiteSkipped('[PHPCR] Error when initializing dbal: '.$output);

                return;
            }
        }

        // initialize repositories
        $process = $this->processBuilder
            ->setArguments(array_merge($this->prefix, array('doctrine:phpcr:repository:init')))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('[PHPCR] Error when initializing repositories: '.$output);

            return;
        }

        echo '[PHPCR]'.PHP_EOL;
    }

    private function setUpOrmDatabase($suite)
    {
        echo PHP_EOL.PHP_EOL;

        $process = $this->processBuilder
            ->setArguments(array_merge($this->prefix, array('doctrine:schema:drop', '--env=orm', '--force')))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('[ORM] Error when dropping database: '.$output);

            return;
        }

        $process = $this->processBuilder
            ->setArguments(array_merge($this->prefix, array('doctrine:database:create', '--env=orm')))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('[ORM] Error when creating database: '.$output);

            return;
        }

        $process = $this->processBuilder
            ->setArguments(array_merge($this->prefix, array('doctrine:schema:create', '--env=orm')))
            ->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('[ORM] Error when creating schema: '.$output);

            return;
        }

        echo '[ORM]'.PHP_EOL;
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        if (!in_array($suite->getName(), array('phpcr', 'orm'))) {
            return;
        }

        $process = $this->processBuilder
            ->setArguments(array_merge($this->prefix, array('doctrine:database:drop', '--force')))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('Error when dropping database: '.$output);
        }
    }
}

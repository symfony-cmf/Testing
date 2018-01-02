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

use Doctrine\Common\DataFixtures\Purger;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class DatabaseTestListener implements \PHPUnit_Framework_TestListener
{
    protected static $currentSuite;

    private $processCallable;

    private $prefix = [];

    public function __construct(callable $processCallable = null)
    {
        $phpExecutableFinder = new PhpExecutableFinder();
        $phpExecutable = $phpExecutableFinder->find(false);
        if (false === $phpExecutable) {
            throw new \RuntimeException('No PHP executable found on the current system.');
        }

        // Symfony 2.3 does not support array prefix, so we have to implement it ourselves
        $this->prefix = [$phpExecutable, __DIR__.'/../../bin/console'];
        $this->processCallable = $processCallable;
    }

    public function getProcess($arguments)
    {
        if (is_callable($this->processCallable)) {
            $callable = $this->processCallable;

            return $callable($arguments);
        }

        return new Process($arguments);
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
        $process = $this->getProcess(array_merge($this->prefix, ['doctrine:phpcr:init:dbal', '--drop', '--force']));

        $process->run();

        if (!$process->isSuccessful()) {
            // try initializing the old way (Jackalope <1.2)
            $process = $this->getProcess(array_merge($this->prefix, ['doctrine:phpcr:init:dbal', '--drop']));

            $process->run();

            if (!$process->isSuccessful()) {
                $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
                $suite->markTestSuiteSkipped('[PHPCR] Error when initializing dbal: '.$output);

                return;
            }
        }

        // initialize repositories
        $process = $this->getProcess(array_merge($this->prefix, ['doctrine:phpcr:repository:init']));

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

        $process = $this->getProcess(array_merge($this->prefix, ['doctrine:schema:drop', '--env=orm', '--force']));

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('[ORM] Error when dropping database: '.$output);

            return;
        }

        $process = $this->getProcess(array_merge($this->prefix, ['doctrine:database:create', '--env=orm']));

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('[ORM] Error when creating database: '.$output);

            return;
        }

        $process = $this->getProcess(array_merge($this->prefix, ['doctrine:schema:create', '--env=orm']));
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
        if (!in_array($suite->getName(), ['phpcr', 'orm'])) {
            return;
        }

        $process = $this->getProcess(array_merge($this->prefix, ['doctrine:database:drop', '--force']));

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $suite->markTestSuiteSkipped('Error when dropping database: '.$output);
        }
    }
}

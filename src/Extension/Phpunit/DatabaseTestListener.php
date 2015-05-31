<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Testing\Extension\Phpunit;

use Symfony\Cmf\Component\Testing\Exception\ManagerNotFoundException;
use Symfony\Cmf\Component\Testing\Database\Manager\ManagerInterface;
use Symfony\Cmf\Component\Testing\Database\Manager\OrmManager;
use Symfony\Cmf\Component\Testing\Database\Manager\PhpcrManager;
use Symfony\Cmf\Component\Testing\Exception\SetupFailedException;
use Symfony\Cmf\Component\Testing\RequiresDatabaseInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;
use Doctrine\Common\DataFixtures\Purger;

class DatabaseTestListener implements \PHPUnit_Framework_TestListener
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;
    private $prefix = array();
    private $initializedDatabases = array();
    private $needsDrop = false;
    private $currentDriver;
    /**
     * @var ManagerInterface[]
     */
    private $managers = array();

    public function __construct($processBuilder = null, array $managers = array())
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

        $this->managers = array(
            'phpcr' => new PhpcrManager(),
            'orm'   => new OrmManager(),
        );

        $this->addManagers($managers);
    }

    /**
     * @param ManagerInterface[] $managers
     */
    public function addManagers(array $managers, $override = false)
    {
        foreach ($managers as $manager) {
            if (isset($this->managers[$manager->getDriver()]) && !$override) {
                continue;
            }

            $this->managers[$manager->getDriver()] = $manager;
        }
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase && !$test instanceof RequiresDatabaseInterface) {
            return;
        }

        $this->currentDriver = $test->getDatabaseDriverName();

        $manager = $this->getManager($this->currentDriver);
        $test->setDbManager($manager);

        $this->setUpDatabaseIfNeeded($manager, $test);

        $manager->purgeDatabase();
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        if (!$this->needsDrop) {
            return;
        }

        $this->getManager($this->currentDriver)->dropDatabase($this->processBuilder);
    }

    private function setUpDatabaseIfNeeded(ManagerInterface $manager, \PHPUnit_Framework_TestCase $testCase)
    {
        $driver = $manager->getDriver();
        if (in_array($driver, $this->initializedDatabases)) {
            return;
        }

        try {
            $manager->setUpDatabase($this->processBuilder);

            $this->needsDrop = true;
            $this->initializedDatabases[] = $driver;
        } catch (SetupFailedException $e) {
            $testCase->markTestSkipped('['.$driver.'] '.$e->getMessage());
        }
    }

    /**
     * @param $driver
     *
     * @return ManagerInterface
     *
     * @throws ManagerNotFoundException When no manager is found for the driver
     */
    private function getManager($driver)
    {
        $driver = strtolower($driver);

        if (!isset($this->managers[$driver])) {
            throw new ManagerNotFoundException($driver);
        }

        return $this->managers[$driver];
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

    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
    }
}

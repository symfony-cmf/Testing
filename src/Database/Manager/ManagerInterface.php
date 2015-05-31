<?php

namespace Symfony\Cmf\Component\Testing\Database\Manager;

use Doctrine\Common\DataFixtures\Loader;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Cmf\Component\Testing\Exception\DropFailedException;
use Symfony\Cmf\Component\Testing\Exception\SetupFailedException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
interface ManagerInterface
{
    /**
     * Sets up the database by executing some commands.
     *
     * @param ProcessBuilder $processBuilder
     *
     * @throws SetupFailedException When there was an error during the setup
     */
    public function setUpDatabase(ProcessBuilder $processBuilder);

    /**
     * @param ProcessBuilder $processBuilder
     *
     * @throws DropFailedException When something went wrong during dropping
     */
    public function dropDatabase(ProcessBuilder $processBuilder);

    public function purgeDatabase();

    /**
     * @return RegistryInterface
     */
    public function getRegistry();

    public function getOm($managerName = null);

    /**
     * Loads fixture classes.
     *
     * @param string[] $classNames
     */
    public function loadFixtures(array $classNames);

    /**
     * Loads a single fixture.
     *
     * @param Loader $loader
     * @param string $className
     */
    public function loadFixtureClass(Loader $loader, $className);

    /**
     * Returns the driver this manager works for.
     *
     * @return string
     */
    public function getDriver();
}

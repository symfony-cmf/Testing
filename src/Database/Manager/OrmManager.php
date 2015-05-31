<?php

namespace Symfony\Cmf\Component\Testing\Database\Manager;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Cmf\Component\Testing\Exception\SetupFailedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\ProcessBuilder;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class OrmManager extends DoctrineManager
{
    /**
     * @var ORMExecutor
     */
    private $executor;

    /**
     * Gets the Doctrine ManagerRegistry
     *
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        $this->assertContainerIsSet();

        return $this->container->get('doctrine');
    }

    public function setUpDatabase(ProcessBuilder $processBuilder)
    {
        $process = $processBuilder
            ->setArguments(array('doctrine:schema:drop', '--env=orm', '--force'))
            ->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

            throw new SetupFailedException('Error when dropping database: '.$output);
        }

        $process = $processBuilder
            ->setArguments(array('doctrine:database:create', '--env=orm'))
            ->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

            throw new SetupFailedException('Error when creating database: '.$output);
        }

        $process = $processBuilder
            ->setArguments(array('doctrine:schema:create', '--env=orm'))
            ->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

            throw new SetupFailedException('Error when creating schema: '.$output);
        }
    }

    /**
     * Purge the database
     */
    public function purgeDatabase()
    {
        $referenceRepository = new ProxyReferenceRepository($this->getOm());
        $this->getExecutor()->setReferenceRepository($referenceRepository);
        $this->getExecutor()->purge();
    }

    /**
     * Loads fixture classes.
     *
     * @param string[] $classNames
     */
    public function loadFixtures(array $classNames)
    {
        $this->assertContainerIsSet();

        $this->purgeDatabase();

        $loader = new ContainerAwareLoader($this->container);

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $this->getExecutor()->execute($loader->getFixtures(), true);
    }

    /**
     * Loads a single fixture.
     *
     * @param Loader $loader
     * @param string $className
     */
    public function loadFixtureClass(Loader $loader, $className)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Fixture class "%s" does not exist.',
                $className
            ));
        }

        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            unset($fixture);
            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($loader, $dependency);
            }
        }
    }

    /**
     * Return the ORM Executor class
     *
     * @return ORMExecutor
     */
    private function getExecutor()
    {
        if ($this->executor) {
            return $this->executor;
        }

        $purger = new ORMPurger();
        $this->executor = new ORMExecutor($this->getOm(), $purger);

        return $this->executor;
    }

    public function getDriver()
    {
        return 'orm';
    }

    private function assertContainerIsSet()
    {
        if (null === $this->container) {
            throw new \BadMethodCallException('This method cannot be executed without a container.');
        }
    }
}

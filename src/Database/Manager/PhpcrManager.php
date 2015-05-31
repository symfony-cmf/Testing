<?php

namespace Symfony\Cmf\Component\Testing\Database\Manager;

use Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Cmf\Component\Testing\Exception\SetupFailedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class PhpcrManager extends DoctrineManager
{
    protected $om;

    /**
     * @var PHPCRExecutor
     */
    private $executor;

    public function setUpDatabase(ProcessBuilder $processBuilder)
    {
        // initialize PHPCR DBAL (new way)
        $process = $processBuilder
            ->setArguments(array('doctrine:phpcr:init:dbal', '--drop', '--force'))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            // try initializing the old way (Jackalope <1.2)
            $process = $processBuilder
                ->setArguments(array('doctrine:phpcr:init:dbal', '--drop'))
                ->getProcess();

            $process->run();

            if (!$process->isSuccessful()) {
                $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

                throw new SetupFailedException('Error when initializing DBAL: '.$output);
            }
        }

        // initialize repositories
        $process = $processBuilder
            ->setArguments(array('doctrine:phpcr:repository:init'))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

            throw new SetupFailedException('Error when initializing repositories: '.$output);
        }
    }

    /**
     * Return the PHPCR ODM registry.
     *
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        $this->assertContainerIsSet();

        return $this->container->get('doctrine_phpcr');
    }

    /**
     * Purge the database.
     *
     * @param boolean $initialize If the ODM repository initializers should be executed.
     */
    public function purgeDatabase($initialize = false)
    {
        $purger = new PHPCRPurger();
        $this->getExecutor($initialize)->purge();
    }

    /**
     * Load fixtures
     *
     * @param array $classNames Fixture classes to load
     * @param boolean $initialize  If the ODM repository initializers should be executed.
     */
    public function loadFixtures(array $classNames, $initialize = false)
    {
        $this->assertContainerIsSet();

        $this->purgeDatabase();

        $loader = new ContainerAwareLoader($this->container);

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $this->getExecutor($initialize)->execute($loader->getFixtures(), true);
    }

    /**
     * Load the named fixture class with the given loader.
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
     * Create a test node, if the test node already exists, remove it.
     */
    public function createTestNode()
    {
        $this->assertContainerIsSet();

        $session = $this->container->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        $session->getRootNode()->addNode('test', 'nt:unstructured');

        $session->save();
    }

    /**
     * Return the PHPCR Executor class
     *
     * @return PHPCRExecutor
     */
    private function getExecutor($initialize = false)
    {
        $this->assertContainerIsSet();

        static $lastInitialize = null;

        if ($this->executor && $initialize === $lastInitialize) {
            return $this->executor;
        }

        $initializerManager = $initialize ? $this->container->get('doctrine_phpcr.initializer_manager') : null;
        $purger = new PHPCRPurger();
        $executor = new PHPCRExecutor($this->getOm(), $purger, $initializerManager);
        $referenceRepository = new ProxyReferenceRepository($this->getOm());
        $executor->setReferenceRepository($referenceRepository);

        $this->executor = $executor;
        $lastInitialize = $initialize;

        return $executor;
    }

    public function getDriver()
    {
        return 'phpcr';
    }

    private function assertContainerIsSet()
    {
        if (null === $this->container) {
            throw new \BadMethodCallException('This method cannot be executed without a container.');
        }
    }
}

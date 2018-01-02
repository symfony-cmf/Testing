<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Functional\DbManager;

use Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PHPCR
{
    protected $container;

    protected $om;

    /**
     * @var PHPCRExecutor
     */
    private $executor;

    /**
     * @param ContainerInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Return the PHPCR ODM registry.
     *
     * @return ManagerRegistry
     */
    public function getRegistry()
    {
        return $this->container->get('doctrine_phpcr');
    }

    /**
     * @param null|string $managerName
     *
     * @return DocumentManager
     */
    public function getOm($managerName = null)
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager($managerName);
        }

        return $this->om;
    }

    /**
     * Purge the database.
     *
     * @param bool $initialize if the ODM repository initializers should be executed
     */
    public function purgeRepository($initialize = false)
    {
        $this->getExecutor($initialize)->purge();
    }

    /**
     * Load fixtures.
     *
     * @param array $classNames Fixture classes to load
     * @param bool  $initialize if the ODM repository initializers should be executed
     */
    public function loadFixtures(array $classNames, $initialize = false)
    {
        $loader = new ContainerAwareLoader($this->container);

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $this->getExecutor($initialize)->execute($loader->getFixtures(), false);
    }

    /**
     * Load the named fixture class with the given loader.
     *
     * @param \Doctrine\Common\DataFixtures\Loader $loader
     * @param string                               $className
     */
    public function loadFixtureClass($loader, $className)
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
        $session = $this->container->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        $session->getRootNode()->addNode('test', 'nt:unstructured');

        $session->save();
    }

    /**
     * Return the PHPCR Executor class.
     *
     * @return PHPCRExecutor
     */
    private function getExecutor($initialize = false)
    {
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
}

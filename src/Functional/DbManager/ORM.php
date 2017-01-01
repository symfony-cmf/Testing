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

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * The DbManager for the Doctrine2 ORM.
 *
 * This manager needs the DoctrineBundle to work.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
class ORM
{
    /**
     * @var ORMExecutor
     */
    private $executor;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Gets the Doctrine ManagerRegistry.
     *
     * @return ManagerRegistry
     */
    public function getRegistry()
    {
        return $this->container->get('doctrine');
    }

    /**
     * Gets the Doctrine ObjectManager.
     *
     * @param null $managerName
     *
     * @return ObjectManager
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
    protected function loadFixtureClass(Loader $loader, $className)
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
     * Return the ORM Executor class.
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
}

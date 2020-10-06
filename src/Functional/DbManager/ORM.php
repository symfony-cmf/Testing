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
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getRegistry(): ManagerRegistry
    {
        return $this->container->get('doctrine');
    }

    public function getOm($managerName = null): ObjectManager
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager($managerName);
        }

        return $this->om;
    }

    /**
     * Purge the database.
     */
    public function purgeDatabase(): void
    {
        $referenceRepository = new ProxyReferenceRepository($this->getOm());
        $this->getExecutor()->setReferenceRepository($referenceRepository);
        $this->getExecutor()->purge();
    }

    /**
     * @param string[] $classNames
     */
    public function loadFixtures(array $classNames): void
    {
        $this->purgeDatabase();
        $loader = new ContainerAwareLoader($this->container);

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $this->getExecutor()->execute($loader->getFixtures(), true);
    }

    protected function loadFixtureClass(Loader $loader, string $className): void
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

    private function getExecutor(): ORMExecutor
    {
        if ($this->executor) {
            return $this->executor;
        }

        $purger = new ORMPurger();
        $this->executor = new ORMExecutor($this->getOm(), $purger);

        return $this->executor;
    }
}

<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
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
     * Gets the Doctrine ManagerRegistry
     *
     * @return ManagerRegistry
     */
    public function getRegistry()
    {
        return $this->container->get('doctrine');
    }

    /**
     * Gets the Doctrine ObjectManager
     *
     * @param null $managerName
     * @return ObjectManager
     */
    public function getOm($managerName = null)
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager($managerName);
        }

        return $this->om;
    }

    public function loadFixtures(array $classNames)
    {
        $loader = new ContainerAwareLoader($this->container);;
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->getOm(), $purger);

        $referenceRepository = new ProxyReferenceRepository($this->getOm());

        $executor->setReferenceRepository($referenceRepository);
        $executor->purge();

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $executor->execute($loader->getFixtures(), true);
    }

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
}

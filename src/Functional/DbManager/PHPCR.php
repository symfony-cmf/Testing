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
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PHPCR
{
    protected $container;

    protected ?DocumentManager $om = null;

    private ?PHPCRExecutor $executor = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getRegistry(): ManagerRegistry
    {
        return $this->container->get('doctrine_phpcr');
    }

    public function getOm(?string $managerName = null): DocumentManager
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager($managerName);
        }

        return $this->om;
    }

    public function purgeRepository(bool $initialize = false): void
    {
        $this->getExecutor($initialize)->purge();
    }

    /**
     * @param string[] $classNames Fixture classes to load
     * @param bool     $initialize if the ODM repository initializers should be executed
     */
    public function loadFixtures(array $classNames, bool $initialize = false): void
    {
        $loader = new ContainerAwareLoader($this->container);

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $this->getExecutor($initialize)->execute($loader->getFixtures(), false);
    }

    public function loadFixtureClass(Loader $loader, string $className): void
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
    public function createTestNode(): void
    {
        $session = $this->container->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        $session->getRootNode()->addNode('test', 'nt:unstructured');

        $session->save();
    }

    private function getExecutor($initialize = false): PHPCRExecutor
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

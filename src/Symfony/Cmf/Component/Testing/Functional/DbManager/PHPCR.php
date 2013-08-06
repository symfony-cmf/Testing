<?php

namespace Symfony\Cmf\Component\Testing\Functional\DbManager;

use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;

class PHPCR
{
    protected $container;
    protected $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getRegistry()
    {
        return $this->container->get('doctrine_phpcr');
    }

    public function getOm()
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager();
        }

        return $this->om;
    }

    public function loadFixtures(array $classNames)
    {
        $loader = new ContainerAwareLoader($this->container);;
        $purger = new PHPCRPurger();
        $executor = new PHPCRExecutor($this->getOm(), $purger);

        $referenceRepository = new ProxyReferenceRepository($this->getOm());

        $executor->setReferenceRepository($referenceRepository);
        $executor->purge();

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $executor->execute($loader->getFixtures(), true);
    }

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

    public function createTestNode()
    {
        $session = $this->container->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        $session->getRootNode()->addNode('test', 'nt:unstructured');

        $session->save();
    }
}

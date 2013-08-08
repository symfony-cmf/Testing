<?php

namespace Symfony\Cmf\Component\Testing\Functional\DbManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @return ObjectManager
     */
    public function getOm()
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager();
        }

        return $this->om;
    }
}

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
}

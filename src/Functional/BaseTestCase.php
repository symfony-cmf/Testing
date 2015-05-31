<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Testing\Database\Manager\ManagerInterface;
use Symfony\Cmf\Component\Testing\RequiresDatabaseInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Container;

/**
 * The base class for Functional and Web tests.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class BaseTestCase extends WebTestCase implements RequiresDatabaseInterface
{
    protected $db;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Return the configuration to use when creating the Kernel.
     *
     * The following settings are used:
     *
     *  * environment - The environment to use (defaults to 'phpcr')
     *  * debug - If debug should be enabled/disabled (defaults to true)
     *
     * @return array
     */
    protected function getKernelConfiguration()
    {
        return array();
    }

    /**
     * Gets the container.
     *
     * @return Container
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            $client = $this->createClient($this->getKernelConfiguration());
            $this->container = $client->getContainer();
        }

        return $this->container;
    }

    /**
     * Gets the DbManager.
     *
     * @see self::getDbManager
     */
    protected function db()
    {
        return $this->getDbManager();
    }

    /**
     * Gets the DbManager.
     *
     * @return object
     */
    protected function getDbManager()
    {
        if (null === $this->db) {
            throw new \InvalidArgumentException('No database manager found.');
        }

        return $this->db;
    }

    /**
     * @param ManagerInterface $manager
     */
    public function setDbManager(ManagerInterface $manager)
    {
        if ($manager instanceof ContainerAwareInterface) {
            $manager->setContainer($this->getContainer());
        }

        $this->db = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabaseDriverName()
    {
        return 'phpcr';
    }

    /**
     * {@inheritDoc}
     *
     * This is overriden to set the default environment to 'phpcr'
     */
    protected static function createKernel(array $options = array())
    {
        // default environment is 'phpcr'
        if (!isset($options['environment'])) {
            $options['environment'] = 'phpcr';
        }

        return parent::createKernel($options);
    }
}

<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * The base class for Functional and Web tests.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
abstract class BaseTestCase extends WebTestCase
{
    /**
     * Use this property to save the DbManager.
     */
    protected $db;

    protected $dbManagers = array();

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
    public function getContainer()
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
    public function db($type)
    {
        return $this->getDbManager($type);
    }

    /**
     * Gets the DbManager.
     *
     * @param string $type The Db type
     *
     * @return object
     */
    public function getDbManager($type)
    {
        if (isset($this->dbManagers[$type])) {
            return $this->dbManagers[$type];
        }

        $className = sprintf(
            'Symfony\Cmf\Component\Testing\Functional\DbManager\%s',
            $type
        );

        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Test DBManager "%s" does not exist.',
                $className
            ));
        }

        $dbManager = new $className($this->getContainer());

        $this->dbManagers[$type] = $dbManager;

        return $this->getDbManager($type);
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

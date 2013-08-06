<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected $db;
    protected $dbManagers = array();
    protected $container;

    public function getContainer(array $options = array())
    {
        // second condition: when options changed, recache the new container
        if (null === $this->container || 0 < count($options)) {
            $client = $this->createClient($options);
            $this->container = $client->getContainer();
        }

        return $this->container;
    }

    public function db($type)
    {
        return $this->getDbManager($type);
    }

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
     */
    protected static function createKernel(array $options = array())
    {
        if (null === static::$class) {
            static::$class = static::getKernelClass();
        }

        return new static::$class(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true,
            isset($options['config_filename']) ? $options['config_filename'] : null
        );
    }
}

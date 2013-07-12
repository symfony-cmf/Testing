<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected $db;
    protected $dbManagers = array();
    protected $container;

    public function getContainer()
    {
        if (null === $this->container) {
            $client = $this->createClient();
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
}

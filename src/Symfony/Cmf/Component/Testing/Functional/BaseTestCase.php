<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected $db;
    protected $dbManagers = array();
    protected $containers = array();

    public function getContainer(array $options = array())
    {
        if (0 === count($this->container) || 0 < count($options)) {
            $hash = md5(serialize($options));

            if (isset($this->containers[$hash])) {
                $this->containers['latest'] = $this->containers[$hash];
            }

            $client = $this->createClient($options);
            $this->containers['latest'] = $this->containers[$hash] = $client->getContainer();
        }

        return $this->containers['latest'];
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

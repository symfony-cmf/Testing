<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected $db;
    protected $dbManagers = array();
    protected $settings = array();
    protected $containers = array();

    protected function configure(array $options)
    {
        $this->settings = $options;
    }

    public function getContainer()
    {
        $hash = md5(serialize($this->settings));

        if (!isset($this->containers[$hash])) {
            $client = $this->createClient($this->settings);
            $this->containers[$hash] = $client->getContainer();
        }

        return $this->containers[$hash];
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
        // default environment is 'phpcr'
        if (!isset($options['environment'])) {
            $options['environment'] = 'phpcr';
        }

        parent::createKernel($options);
    }
}

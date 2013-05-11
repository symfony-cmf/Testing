<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected $db;

    public function getContainer()
    {
        $client = $this->createClient();

        return $client->getContainer();
    }

    public function db($type)
    {
        return $this->getDbManager($type);
    }

    public function getDbManager($type)
    {
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

        return $dbManager;
    }
}

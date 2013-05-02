<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseTestCase extends WebTestCase
{
    protected $kernelConfigName = 'default.yml';

    public function setUp()
    {
        self::$kernel = self::createKernel();
        self::$kernel->setConfigFilename($this->kernelConfigName);
        self::$kernel->init();
        self::$kernel->boot();
    }

    public function getContainer()
    {
        return self::$kernel->getContainer();
    }

    public function getApplication()
    {
        $application = new Application(self::$kernel);
        return $application;
    }
}

<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseTestCase extends WebTestCase
{
    protected $kernelConfigName = 'default.yml';

    public static function getKernelClass()
    {
        return 'AppKernel';
    }

    public function setUp()
    {
        // get the kernel loaded.. we don't use autoloading
        // so that we don't have to worry about the bundles
        // namespace.
        $kernelFName = sprintf('%s/%s/%s.php',
            realpath(__DIR__.'/../../../../../../../../..'),
            'Tests/Functional/app',
            self::getKernelClass()
        );

        if (!file_exists($kernelFName)) {
            throw new \Exception('Kernel does not exist: "'.$kernelFName.'"');
        }

        require_once($kernelFName);

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

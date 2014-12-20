<?php

namespace Symfony\Cmf\Component\Testing\Tests\Fixtures;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class TestTestCase extends BaseTestCase
{
    public function setKernel(KernelInterface $kernel)
    {
        self::$kernel = $kernel;
    }

    protected static function createKernel(array $options = array())
    {
        if (null === self::$kernel) {
            return parent::createKernel($options);
        }

        return self::$kernel;
    }
}

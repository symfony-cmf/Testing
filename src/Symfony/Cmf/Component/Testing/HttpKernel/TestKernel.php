<?php

namespace Symfony\Cmf\Component\Testing\HttpKernel;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

abstract class TestKernel extends Kernel
{
    public function getKernelDir()
    {
        $refl = new \ReflectionClass($this);
        $fname = $refl->getFileName();
        $kernelDir = dirname($fname);
        return $kernelDir;
    }

    public function getCacheDir()
    {
        return implode('/', array(
            $this->getKernelDir(),
            'cache'
        ));
    }
}

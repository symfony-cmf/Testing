<?php

namespace Symfony\Cmf\Component\Testing\HttpKernel;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

abstract class TestKernel extends Kernel
{
    protected $configFilename = 'default.yml';

    public function getKernelDir()
    {
        $refl = new \ReflectionClass($this);
        $fname = $refl->getFileName();
        $kernelDir = dirname($fname);
        return $kernelDir;
    }

    public function getConfigFilename()
    {
        $fs = new Filesystem();

        $configFilename = $this->configFilename;

        if (!$fs->isAbsolutePath($configFilename)) {
            $configFilename = implode('/', array(
                $this->getKernelDir(), 
                'config',
                $configFilename
            ));
        }

        if (!file_exists($configFilename)) {
            throw new \RuntimeException(sprintf('The configFilename file "%s" does not exist.', $configFilename));
        }

        return $configFilename;
    }

    public function setConfigFilename($configFilename)
    {
        $this->configFilename = $configFilename;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getConfigFilename());
    }

    public function getCacheDir()
    {
        return implode('/', array(
            $this->getKernelDir(),
            'cache'
        ));
    }
}

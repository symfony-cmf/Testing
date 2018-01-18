<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\HttpKernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebServerBundle\WebServerBundle;
use Symfony\Cmf\Component\Testing\DependencyInjection\Compiler\TestContainerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * TestKernel base class for Symfony CMF Bundle
 * integration tests.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
abstract class TestKernel extends Kernel
{
    protected $bundleSets = [];

    protected $requiredBundles = [];

    /**
     * Register commonly needed bundle sets and then
     * after initializing the parent kernel, let the
     * concrete kernel configure itself using the abstracvt
     * configure() command.
     */
    public function __construct($env, $debug)
    {
        $defaultBundles = [
            FrameworkBundle::class,
            SecurityBundle::class,
            TwigBundle::class,
            MonologBundle::class,
        ];

        if (class_exists(WebServerBundle::class)) {
            $defaultBundles[] = WebServerBundle::class;
        }

        $this->registerBundleSet('default', $defaultBundles);

        $this->registerBundleSet('phpcr_odm', [DoctrineBundle::class, DoctrinePHPCRBundle::class]);
        $this->registerBundleSet('doctrine_orm', [DoctrineBundle::class]);

        parent::__construct($env, $debug);
        $this->configure();
    }

    /**
     * Use this method to declare which bundles are required
     * by the Kernel, e.g.
     *
     *    $this->requireBundleSets('default', 'phpcr_odm');
     *    $this->addBundle(new MyBundle);
     *    $this->addBundles(array(new Bundle1, new Bundle2));
     */
    abstract protected function configure();

    /**
     * Register a set of bundles with the given name.
     *
     * This method does not add the bundles to the kernel,
     * it just makes a set available.
     */
    public function registerBundleSet($name, $bundles)
    {
        $this->bundleSets[$name] = $bundles;
    }

    /**
     * The bundles in the named sets will be added to the Kernel.
     */
    public function requireBundleSets(array $names)
    {
        foreach ($names as $name) {
            $this->requireBundleSet($name);
        }
    }

    /**
     * Require the bundles in the named bundle set.
     *
     * Note that we register the FQN's and not the concrete classes.
     * This enables us to declare pre-defined bundle sets without
     * worrying if the bundle is actually present or not.
     */
    public function requireBundleSet($name)
    {
        if (!isset($this->bundleSets[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Bundle set %s has not been registered, available bundle sets: %s',
                $name,
                implode(',', array_keys($this->bundleSets))
            ));
        }

        foreach ($this->bundleSets[$name] as $bundle) {
            if (!class_exists($bundle)) {
                throw new \InvalidArgumentException(sprintf(
                    'Bundle class "%s" does not exist.',
                    $bundle
                ));
            }

            $this->requiredBundles[$bundle] = new $bundle();
        }
    }

    /**
     * Add concrete bundles to the kernel.
     */
    public function addBundles(array $bundles)
    {
        foreach ($bundles as $bundle) {
            $this->addBundle($bundle);
        }
    }

    /**
     * Add a concrete bundle to the kernel.
     */
    public function addBundle(BundleInterface $bundle)
    {
        $this->requiredBundles[] = $bundle;
    }

    /**
     * {inheritDoc}.
     *
     * Here we return our list of bundles
     */
    public function registerBundles()
    {
        return $this->requiredBundles;
    }

    /**
     * Returns the KernelDir of the CHILD class,
     * i.e. the concrete implementation in the bundles
     * src/ directory (or wherever).
     */
    public function getKernelDir()
    {
        $refl = new \ReflectionClass($this);
        $fname = $refl->getFileName();
        $kernelDir = dirname($fname);

        return $kernelDir;
    }

    public function getCacheDir()
    {
        return implode('/', [
            $this->getKernelDir(),
            'var',
            'cache',
        ]);
    }

    public function getLogDir()
    {
        return implode('/', [
            $this->getKernelDir(),
            'var',
            'logs',
        ]);
    }

    /**
     * Registers the bundles defined in config/bundles.php.
     */
    protected function registerConfiguredBundles()
    {
        $bundleFilePath = $this->getKernelDir().'/config/bundles.php';
        if (!file_exists($bundleFilePath)) {
            return;
        }

        $bundles = require $bundleFilePath;
        foreach ($bundles as $class => $environments) {
            if (isset($environments['all']) || isset($environments[$this->environment])) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Bundle class "%s" does not exist.',
                        $class
                    ));
                }

                $this->requiredBundles[$class] = new $class();
            }
        }
    }

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);
        if (in_array($this->getEnvironment(), ['test', 'phpcr']) && file_exists($this->getKernelDir().'/config/public_services.php')) {
            $services = require $this->getKernelDir().'/config/public_services.php';
            $container->addCompilerPass(new TestContainerPass($services), PassConfig::TYPE_OPTIMIZE);
        }
    }
}

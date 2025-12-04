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
    protected array $bundleSets = [];

    protected array $requiredBundles = [];

    /**
     * Register commonly needed bundle sets and then
     * after initializing the parent kernel, let the
     * concrete kernel configure itself using the abstracvt
     * configure() command.
     */
    public function __construct(string $env, bool $debug)
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
    abstract protected function configure(): void;

    /**
     * Register a set of bundles with the given name.
     *
     * This method does not add the bundles to the kernel,
     * it just makes a set available.
     */
    public function registerBundleSet(string $name, array $bundles): void
    {
        $this->bundleSets[$name] = $bundles;
    }

    /**
     * The bundles in the named sets will be added to the Kernel.
     *
     * @param string[] $names
     */
    public function requireBundleSets(array $names): void
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
    public function requireBundleSet(string $name): void
    {
        if (!isset($this->bundleSets[$name])) {
            throw new \InvalidArgumentException(\sprintf(
                'Bundle set %s has not been registered, available bundle sets: %s',
                $name,
                implode(',', array_keys($this->bundleSets))
            ));
        }

        foreach ($this->bundleSets[$name] as $bundle) {
            if (!class_exists($bundle)) {
                throw new \InvalidArgumentException(\sprintf(
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
    public function addBundles(array $bundles): void
    {
        foreach ($bundles as $bundle) {
            $this->addBundle($bundle);
        }
    }

    /**
     * Add a concrete bundle to the kernel.
     */
    public function addBundle(BundleInterface $bundle): void
    {
        $this->requiredBundles[] = $bundle;
    }

    /**
     * {@inheritDoc}.
     *
     * Here we return our list of bundles
     */
    public function registerBundles(): iterable
    {
        return $this->requiredBundles;
    }

    /**
     * Returns the project directory of the CHILD class,
     * i.e. the concrete implementation in the bundles
     * src/ directory (or wherever).
     */
    public function getProjectDir(): string
    {
        $refl = new \ReflectionClass($this);
        $fname = $refl->getFileName();

        return \dirname($fname);
    }

    public function getCacheDir(): string
    {
        return implode('/', [
            $this->getProjectDir(),
            'var',
            'cache',
        ]);
    }

    public function getLogDir(): string
    {
        return implode('/', [
            $this->getProjectDir(),
            'var',
            'logs',
        ]);
    }

    /**
     * Registers the bundles defined in config/bundles.php.
     */
    protected function registerConfiguredBundles(): void
    {
        $bundleFilePath = $this->getProjectDir().'/config/bundles.php';
        if (!file_exists($bundleFilePath)) {
            return;
        }

        $bundles = require $bundleFilePath;
        foreach ($bundles as $class => $environments) {
            if (isset($environments['all']) || isset($environments[$this->environment])) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Bundle class "%s" does not exist.',
                        $class
                    ));
                }

                $this->requiredBundles[$class] = new $class();
            }
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
        if (\in_array($this->getEnvironment(), ['test', 'phpcr']) && file_exists($this->getProjectDir().'/config/public_services.php')) {
            $services = require $this->getProjectDir().'/config/public_services.php';
            $container->addCompilerPass(new TestContainerPass($services), PassConfig::TYPE_OPTIMIZE);
        }
    }
}

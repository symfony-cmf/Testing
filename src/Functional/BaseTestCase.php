<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Functional;

use Doctrine\Bundle\PHPCRBundle\Test\RepositoryManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Testing\Functional\DbManager\ORM;
use Symfony\Cmf\Component\Testing\Functional\DbManager\PHPCR;
use Symfony\Cmf\Component\Testing\Functional\DbManager\PhpcrDecorator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The base class for Functional and Web tests.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
abstract class BaseTestCase extends WebTestCase
{
    /**
     * Use this property to save the DbManagers.
     *
     * @var array
     */
    protected $dbManagers = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * Return the configuration to use when creating the Kernel.
     *
     * The following settings are used:
     *
     *  * environment - The environment to use (defaults to 'phpcr')
     *  * debug - If debug should be enabled/disabled (defaults to true)
     */
    protected static function getKernelConfiguration(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Overwritten to set the default environment to 'phpcr'.
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        // default environment is 'phpcr'
        if (!isset($options['environment'])) {
            $options['environment'] = 'phpcr';
        }

        return parent::createKernel($options);
    }

    /**
     * {@inheritdoc}
     *
     * Overwritten to set the kernel configuration from getKernelConfiguration.
     */
    protected static function bootKernel(array $options = []): KernelInterface
    {
        return parent::bootKernel(static::getKernelConfiguration());
    }

    /**
     * BC with Symfony < 5.3 - when minimum version raises to ^5.3, we can remove this method.
     */
    protected static function getContainer(): Container
    {
        if (method_exists(KernelTestCase::class, 'getContainer')) {
            return parent::getContainer();
        }

        return self::getKernel()->getContainer();
    }

    protected static function getKernel(): KernelInterface
    {
        if (null === static::$kernel) {
            static::bootKernel();
        }

        if (static::$kernel instanceof KernelInterface) {
            $kernelEnvironment = static::$kernel->getEnvironment();
            $expectedEnvironment = isset(static::getKernelConfiguration()['environment'])
                ? static::getKernelConfiguration()['environment']
                : 'phpcr';
            if ($kernelEnvironment !== $expectedEnvironment) {
                var_dump($kernelEnvironment, $expectedEnvironment);
                static::bootKernel();
            }
        }

        if (!static::$kernel->getContainer()) {
            static::$kernel->boot();
        }

        return static::$kernel;
    }

    /**
     * @return Client|KernelBrowser
     */
    protected function getFrameworkBundleClient()
    {
        if (null === $this->client) {
            // property does not exist in all symfony versions
            if (property_exists(self::class, 'booted') && self::$booted) {
                self::ensureKernelShutdown();
            }
            $this->client = self::createClient($this->getKernelConfiguration());
        }

        return $this->client;
    }

    /**
     * Shortcut for getDbManager.
     *
     * @see self::getDbManager
     */
    protected function db($type)
    {
        return $this->getDbManager($type);
    }

    /**
     * @return ORM|PHPCR
     */
    protected function getDbManager(string $type)
    {
        if (isset($this->dbManagers[$type])) {
            return $this->dbManagers[$type];
        }

        $className = sprintf(
            'Symfony\Cmf\Component\Testing\Functional\DbManager\%s',
            $type
        );

        if ('phpcr' === strtolower($type) && class_exists(RepositoryManager::class)) {
            $className = PhpcrDecorator::class;
        }

        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Test DBManager "%s" does not exist.',
                $className
            ));
        }

        $dbManager = new $className($this->getContainer());

        $this->dbManagers[$type] = $dbManager;

        return $dbManager;
    }

    protected static function assertResponseSuccess(Response $response)
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXPath($dom);
        $result = $xpath->query('//div[contains(@class,"text-exception")]/h1');
        $exception = null;
        if ($result->length) {
            $exception = $result->item(0)->nodeValue;
        }

        self::assertEquals(200, $response->getStatusCode(), $exception ? 'Exception: "'.$exception.'"' : '');
    }
}

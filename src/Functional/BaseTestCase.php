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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Testing\Functional\DbManager\PhpcrDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
    protected function getKernelConfiguration(): array
    {
        return [];
    }

    public function getFrameworkBundleClient(): Client
    {
        if (null === $this->client) {
            // property does not exist in all symfony versions
            if (\property_exists(self::class, 'booted') && self::$booted) {
                self::ensureKernelShutdown();
            }
            $this->client = self::createClient($this->getKernelConfiguration());
        }

        return $this->client;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->getKernel()->getContainer();
    }

    public function getKernel(): KernelInterface
    {
        if (null === static::$kernel) {
            parent::bootKernel(static::getKernelConfiguration());
        }

        if (static::$kernel instanceof  KernelInterface) {
            $kernelEnvironment = static::$kernel->getEnvironment();
            $expectedEnvironment = isset($this->getKernelConfiguration()['environment'])
                ? $this->getKernelConfiguration()['environment']
                : 'phpcr';
            if ($kernelEnvironment !== $expectedEnvironment) {
                parent::bootKernel(static::getKernelConfiguration());
            }
        }

        if (!static::$kernel->getContainer()) {
            static::$kernel->boot();
        }

        return static::$kernel;
    }

    /**
     * Gets the DbManager.
     *
     * @see self::getDbManager
     */
    public function db($type)
    {
        return $this->getDbManager($type);
    }

    /**
     * Gets the DbManager.
     *
     * @param string $type The Db type
     *
     * @return object
     */
    public function getDbManager($type)
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

    public static function getKernelClass(): string
    {
        if (isset($_SERVER['KERNEL_CLASS']) || isset($_ENV['KERNEL_CLASS'])) {
            $class = isset($_SERVER['KERNEL_CLASS']) ? $_SERVER['KERNEL_CLASS'] : $_ENV['KERNEL_CLASS'];
            if (!class_exists($class)) {
                throw new \RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the %s::createKernel() method.', $class, static::class));
            }

            return $class;
        }

        return parent::getKernelClass();
    }

    /**
     * {@inheritdoc}
     *
     * This is overriden to set the default environment to 'phpcr'
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        // default environment is 'phpcr'
        if (!isset($options['environment'])) {
            $options['environment'] = 'phpcr';
        }

        return parent::createKernel($options);
    }

    protected function assertResponseSuccess(Response $response)
    {
        libxml_use_internal_errors(true);

        $dom = new \DomDocument();
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXpath($dom);
        $result = $xpath->query('//div[contains(@class,"text-exception")]/h1');
        $exception = null;
        if ($result->length) {
            $exception = $result->item(0)->nodeValue;
        }

        $this->assertEquals(200, $response->getStatusCode(), $exception ? 'Exception: "'.$exception.'"' : '');
    }
}

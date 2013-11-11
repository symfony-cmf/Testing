<?php

namespace Symfony\Cmf\Component\Testing\Unit;

use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * A class to test the Configuration class of a bundle.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class ConfigurationTestCase extends AbstractConfigurationTestCase
{
    /**
     * Returns the filenames to test.
     *
     * It should return an array with the format and its file:
     *
     *     Array(
     *       [yaml] => 'path/to/fixtures/config.yml',
     *       [xml]  => 'path/to/fixtures/config.xml',
     *     )
     *
     * Supported formats are yaml, xml and php.
     *
     * It can also return multiple files per format to test: 
     *
     *     Array(
     *       [yaml] => array('config-1.yml', 'config-2.yml')
     *     )
     * 
     * @return array
     */
    abstract protected function getFilenames();

    /**
     * Returns an array with the expected result after parsing the config.
     *
     * @return array
     */
    abstract protected function getExpectedResult();

    /**
     * Returns the Extension class of the component.
     *
     * @return ExtensionInterface
     */
    abstract protected function getExtension();

    public function testYamlConfig()
    {
        $filenames = $this->getFilenames();

        if (!isset($filenames['yaml'])) {
            $this->markTestSkipped('No Yaml configuration fixture configured');
        }

        $files = $filenames['yaml'];
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $this->assertProcessedConfigurationEquals($this->loadYamlFile($file), $this->getExpectedResult());
        }
    }

    public function testXmlConfig()
    {
        $filenames = $this->getFilenames();

        if (!isset($filenames['xml'])) {
            $this->markTestSkipped('No XML configuration fixture configured');
        }

        $files = $filenames['xml'];
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $this->assertProcessedConfigurationEquals($this->loadXmlFile($file), $this->getExpectedResult());
        }
    }

    public function testPhpConfig()
    {
        $filenames = $this->getFilenames();

        if (!isset($filenames['php'])) {
            $this->markTestSkipped('No PHP configuration fixture configured');
        }

        $files = $filenames['php'];
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $this->assertProcessedConfigurationEquals($this->loadPhpFile($file), $this->getExpectedResult());
        }
    }

    /**
     * Loads a XML file.
     *
     * @param string $file The file name
     */
    protected function loadXmlFile($file)
    {
        return $this->doLoadFile($file, 'XmlFileLoader');
    }

    /**
     * Loads a Yaml file.
     *
     * @param string $file The file name
     */
    protected function loadYamlFile($file)
    {
        return $this->doLoadFile($file, 'YamlFileLoader');
    }

    /**
     * Loads a PHP file.
     *
     * @param string $file The file name
     */
    protected function loadPhpFile($file)
    {
        return $this->doLoadFile($file, 'PhpFileLoader');
    }

    private function doLoadFile($file, $loader)
    {
        $container = new ContainerBuilder();

        $extension = $this->getExtension();
        $container->registerExtension($extension);

        $loader = 'Symfony\Component\DependencyInjection\Loader\\'.$loader;
        $loader = new $loader($container, new FileLocator(dirname($file)));
        $loader->load(basename($file));

        return $container->getExtensionConfig($extension->getAlias());
    }
}

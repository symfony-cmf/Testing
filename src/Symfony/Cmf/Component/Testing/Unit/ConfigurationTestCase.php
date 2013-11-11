<?php

namespace Symfony\Cmf\Component\Testing\Unit;

use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class ConfigurationTestCase extends AbstractConfigurationTestCase
{
    abstract protected function getFilenames();
    abstract protected function getExpectedResult();
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

    public function testXmlConfig()
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

    protected function loadXmlFile($file)
    {
        return $this->doLoadFile($file, 'XmlFileLoader');
    }

    protected function loadYamlFile($file)
    {
        return $this->doLoadFile($file, 'YamlFileLoader');
    }

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

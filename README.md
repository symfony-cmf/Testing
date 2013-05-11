[WIP] Symfony CMF Testing Component
===================================

**NOTE**: This is an internal tool and is not intended to be used outside of
the context of the CMF!

This is a testing library created to aid the development of functional tests
for the Symfony CMF Bundle suite.

Install the Dependencies
------------------------

Use composer to install the dependencies required to be able to run the
functional test suite:

````
composer install --dev
````

PHPUnit configuration
---------------------

Copy the `phpunit.dist.xml` file from the testing component to the directory
root of the bundle:

````
cp vendor/symfony-cmf/testing/skeleton/phpunit.xml.dist .
````

Note that this file includes the bootstrap file `bootstrap/bootstrap.php`
which initializes the autoloader and defines some useful PHP constants.

Create your configuration class
-------------------------------

You can include pre-defined configurations from the testing component as
follows:

````php
// YourBundle/Tests/Functional/app/config/config.php
<?php

$loader->import(CMF_TEST_CONFIG_DIR.'/sonata_admin.php');
````

We have to use a PHP file to access the `CMF_TEST_CONFIG_DIR` constant
which is defined in the bootstrap file. Have a look in the 
`/skeleton/app/config` directory for all possible options.

Create the test Kernel
----------------------

Below is an example test kernel. Note that you extend `TestKernel` and need to
implement the `configure` method to register any bundles that you need.

You should use the `requireBundleSets` method to register pre-defined sets of
bundles, e.g. `sonata_admin` will include all the bundles required for a
standard CMF sonata admin interface.

For bundles specific to this test kernel or to the bundle as a whole, use the
`addBundles` method.

````php
// YourBundle/Tests/Functional/app/AppKernel.php
<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSets(array(
            'default', 'phpcr_odm', 'sonata_admin'
        ));

        $this->addBundles(array(
            new \Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new \Symfony\Cmf\Bundle\MenuBundle\SymfonyCmfMenuBundle(),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // Load our configuration
        $loader->load(__DIR__.'/config/config.php');
    }

}
````

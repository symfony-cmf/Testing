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

Bootstrapping
-------------

To generate a skeleton bootstrap:

````
$ php vendor/symfony-cmf/testing/bin/generate_functional.php
````

This generates the following files:

````
./
./Tests/Functional/app/AppKernel.php
./Tests/Functional/app/console
./Tests/Functional/app/config
./Tests/Functional/app/config/default.yml
./Tests/Functional/app/config/parameters.yml.dist
./Tests/Functional/app/config/dist
./Tests/Functional/app/config/dist/monolog.yml
./Tests/Functional/app/config/dist/framework.yml
./Tests/Functional/app/config/dist/phpcrodm.yml
./Tests/Functional/app/config/dist/routing.yml
./Tests/Functional/app/config/dist/doctrine.yml
./Tests/Functional/app/config/user
````

You will need to copy `parameters.yml.dist` to `parameters.yml` for the kernel
to work, the distribution configuration should work out-of-the-box with
doctrine dbal sqlite.

Pull in some other files
------------------------

The following files are also useful:

````
cp vendor/symfony-cmf/testing/skeleton/.travis.yml .
cp vendor/symfony-cmf/testing/skeleton/phpunit.dist.xml .
````

Writing Functional Test Cases
-----------------------------

Most of the functional test cases in the CMF currently use PHCR-ODM. This
component provides a base test case which automatically purges and creates
the node `/test`. You should place any documents your test will create under
this node.

Example:

````
<?php

namespace ...
use Symfony\Cmf\Component\Testing\Functional\PhpcrOdmTestCase;

class MyTest extends PhpcrOdmTestCase
{
    public function testFooIsCreated()
    {
        $foo = new FooDocument;
        $foo->id = '/test/foo';
        $this->getDm()->persist($foo);
        $this->getDm()->clear();
        $foo = $this->getDm()->find(null, '/test/foo');

        $this->assertNotNull($foo); // will pass
    }

    public function testFooIsGone()
    {
        $foo = $this->getDm()->find(null, '/test/foo');
        $this->assertNull($foo); // will pass
    }
}
````

There is also a base test case which is storage layer agnostic:

````
<?php

namespace ...
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class MyTest extends PhpcrOdmTestCase
{
    public function testWhatever()
    {
        $myService = $this->getContainer()->get('foo');
        $application = $this->getApplication();
    }
}
````


Changing the Kernel
-------------------

You may want to have a test use a different kernel then the default
`AppKernel` - for example, you might want to test optional dependencies such
as SonataAdmin.

To do this simply override `getKernelClassname`:

````
// mytest.php
public static function getKernelClassname()
{
    return 'AlternativeKernel';
}
````

The kernel file `AlternativeKernel.php` must live inside the directory
`Tests/Functional/app`. We avoid the use of the autoloader to avoid having
to guess the bundles namespace.

Using the test console
----------------------

The test console can be invaluable for debugging tests, you can use it as
follows:

````
$ php Tests/Functional/app/console
````

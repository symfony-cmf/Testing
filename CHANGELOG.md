Changelog
=========

2.0.1
-----

 * **2018-01-02**: Remove dependency on symfony/symfony in favor of more specific packages. Its a
   bug of the package using testing if it does not declare its dependencies.

2.0.0
-----

2.0.0-RC2
---------

 * **2017-01-25**: Added WebServerBundle support
 * **2017-01-25**: Dropped PHP <5.6 support

2.0.0-RC1
---------

 * **2016-06-21**: [BC BREAK] Deleted the `sonata_admin` bundle set
 * **2016-06-21**: [BC BREAK] Deleted the `LoadBaseData` data fixtures
 * **2016-06-21**: [BC BREAK] Deleted the `Content` document
 * **2016-06-21**: [BC BREAK] Dropped PHP <5.5 support
 * **2016-06-21**: [BC BREAK] Dropped Symfony <2.8 support

1.3.0
-----

1.3.0-RC1
---------

* **2015-10-17**: Symfony 3 is supported
* **2015-10-17**: [BC Break] `resources/config/dist/framework.yml` has been
                  renamed to `resources/config/dist/framework.php`
* **2015-04-26**: Added `BaseTestCase::assertResponseSuccess()` to provide
                  helpfull output in case the response errored
* **2015-04-19**: The package now requires `symfony/phpunit-bridge`
* **2015-01-18**: Added required `--force` for the `doctrine:phpcr:init:dbal`
                  command to support Jackalope 1.2

1.2.0-RC2
---------

* **2014-10-06**: Added purgeDatabase method to ORM manager

1.2.0-RC1
---------

* **2014-07-27**: Added DataFixture support to the ORM DbManager
* **2014-06-16**: Initializer for phpcr fixture loading
* **2014-06-06**: Updated to PSR-4 autoloading

1.1.0-RC2
---------

* **2014-06-11**: fetch manager by its name
* **2014-04-24**: [DEPRECATE] Deprecated the `sonata_admin` bundle set
* **2014-04-24**: added bundle sets: `sonata_admin_orm` and `sonata_admin_phpcr`
* **2014-04-11**: dropped Symfony 2.2 compatibility

1.1.0-RC1
---------

* **2013-04-07**: [DEPRECATE] Deprecated `LoadBaseData` DataFixture, it will be removed in 2.0
* **2013-04-02**: [DEPRECATE] Deprecated `Content` document, it will be removed in 2.0
* **2013-12-27**: Added `XmlSchemaTestCase` to test XML schema's
* **2013-11-17**: Added `DatabaseTestListener` to support database testing

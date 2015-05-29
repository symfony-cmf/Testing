How to upgrade to from 1.x to 2.0
=================================

Assertions
----------

* The `BaseTestCase#assertResponseOk()` method was moved and renamed to `Assert#responseOk()`.

  **Before**
  ```php
  // ...
  use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

  class SomePageTestCase extends BaseTestCase
  {
      // ...
      public function testPage()
      {
          // ...
          $client->request('GET', '/');

          $this->assertResponseOk($client->getResponse());
      }
  }
  ```

  **After**
  ```php
  // ...
  use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
  use Symfony\Cmf\Component\Testing\Extension\Assert;

  class SomePageTestCase extends BaseTestCase
  {
      // ...
      public function testPage()
      {
          // ...
          $client->request('GET', '/');

          Assert::responseOk($client->getResponse());
      }
  }
  ```

* The assertions in `XmlSchemaTestCase` have been moved to the PHPUnit `Assert` class.

  **Before**
  ```php
  // ...
  use Symfony\Cmf\Component\Testing\Unit\XmlSchemaTestCase;

  class XmlSchemaTest extends XmlSchemaTestCase
  {
      public function testSchema()
      {
          // ...

          $this->assertSchemaAcceptsXml($dom, __DIR__.'/../Resources/config/schema-1.0.xsd');
          // or
          // $this->assertSchemaRefusesXml($dom, __DIR__.'/../Resources/config/schema-1.0.xsd');
      }
  }
  ```

  **After**
  ```php
  // ...
  use Symfony\Cmf\Component\Testing\Extension\Phpunit\Assert;

  class XmlSchemaTest extends \PHPUnit_Framework_TestCase
  {
      public function testSchema()
      {
          // ...

          Assert::schemaAcceptsXml($dom, __DIR__.'/../Resources/config/schema-1.0.xsd');
          // or
          // Assert::schemaRefusesXml($dom, __DIR__.'/../Resources/config/schema-1.0.xsd');
      }
  }
  ```

PHPCR Implementations
---------------------

* The `Content` document was removed, include it in your project's test fixtures instead.
* The `LoadBaseData` data fixture was removed, include it in your project's data fixtures instead.
* The `sonata_admin` bundle set was removed, use `sonata_admin_orm` or `sonata_admin_phpcr` instead.


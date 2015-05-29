How to upgrade to from 1.x to 2.0
=================================

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

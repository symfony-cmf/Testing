# Upgrade from 1.x to 2.0

## Sonata Admin

* With the movement of all sonata related classes and code from symfony-cmf core bundles to
  sonata-admin-integration-bundle `sonata_admin` as a bundle set isn't needed anymore. When you still
  need the bundles defined by this set, you have to register them manually.

## General

* The `LoadBaseData` fixture loader was removed. You have to initialize your test nodes in your own fixtures now.
* Symfony versions up from 3.0 will be supported now.
* `Symfony\Cmf\Component\Testing\Document\Content` as a general test document was removed, so you have to create own
  testing document classes now.

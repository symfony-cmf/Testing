# Upgrade from 1.x to 2.0

## Bundle sets

 * The `sonata_admin` bundle set was removed. Use the `sonata_admin_orm` or
   `sonata_admin_phpcr` set instead.

## Fixtures

 * The `LoadBaseData` fixture loader was removed. You have to initialize your
   test nodes in your own fixtures now.

 * `Symfony\Cmf\Component\Testing\Document\Content` as a general test document
   was removed, so you have to create own testing document classes now.

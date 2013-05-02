<?php

$vendorDir = __DIR__.'/../../../../../..';
$appDir = __DIR__.'/../../../../../../..'.SF_TEST_KERNEL_ROOT;

$file = $vendorDir.'/autoload.php';

if (!file_exists($file)) {
    throw new RuntimeException('Run "composer update --dev" to run test suite.');
}

require_once $file;

if ( !class_exists('Symfony\\Component\\ClassLoader\\UniversalClassLoader')) {
    throw new RuntimeException('Run "composer update --dev" to run test suite. (You seem to have missed the --dev part.)');
}

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = new UniversalClassLoader();

AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});

AnnotationRegistry::registerFile($vendorDir.'/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/Mapping/Annotations/DoctrineAnnotations.php');


<?php

$vendorDir = realpath(__DIR__.'/../../..');

$file = $vendorDir.'/autoload.php';

if (!file_exists($file)) {
    throw new RuntimeException('Cannot find "'.$file.'". Run "composer update --dev" to run test suite.');
}

require_once $file;

if (!class_exists('Symfony\\Component\\ClassLoader\\UniversalClassLoader')) {
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

define('CMF_TEST_ROOT_DIR', realpath(__DIR__.'/..'));
define('CMF_TEST_CONFIG_DIR', CMF_TEST_ROOT_DIR.'/resources/config');

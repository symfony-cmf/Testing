<?php

$kernelRootDir = $container->getParameter('kernel.root_dir');
$bundleName = null;

if (preg_match('&/([a-zA-Z]+?)Bundle&', $kernelRootDir, $matches)) {
    $bundleName = $matches[1].'Bundle';
    $bundleFQN = 'Symfony\\Cmf\\Bundle\\'.$matches[1].'Bundle';
    $container->setParameter('cmf_testing.bundle_name', $bundleName);
    $container->setParameter('cmf_testing.bundle_fqn', $bundleFQN);
}

$loader->import('dist/parameters.yml');
$loader->import('dist/framework.yml');
if (class_exists('Symfony\Bundle\MonologBundle\MonologBundle')) {
    $loader->import('dist/monolog.yml');
}
$loader->import('dist/doctrine.yml');
$loader->import('dist/security.yml');

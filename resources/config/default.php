<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$kernelRootDir = $container->hasParameter('kernel.project_dir') ? $container->getParameter('kernel.project_dir') : $container->getParameter('kernel.root_dir');
$bundleName = null;

if (preg_match('&/([a-zA-Z]+?)Bundle&', $kernelRootDir, $matches)) {
    $bundleName = $matches[1].'Bundle';
    $bundleFQN = 'Symfony\\Cmf\\Bundle\\'.$matches[1].'Bundle';
    if (!$container->hasParameter('cmf_testing.bundle_name')) {
        $container->setParameter('cmf_testing.bundle_name', $bundleName);
    }
    if (!$container->hasParameter('cmf_testing.bundle_fqn')) {
        $container->setParameter('cmf_testing.bundle_fqn', $bundleFQN);
    }
}

if ($container->hasParameter('kernel.project_dir')) {
    $loader->import(__DIR__.'/dist/parameters_sf5.yml');
} else {
    $loader->import(__DIR__.'/dist/parameters.yml');
}
if (class_exists('Symfony\Bundle\MonologBundle\MonologBundle')) {
    $loader->import(__DIR__.'/dist/monolog.yml');
}
$loader->import(__DIR__.'/dist/doctrine.yml');
$loader->import(__DIR__.'/dist/framework.php');
$loader->import(__DIR__.'/dist/security.php');

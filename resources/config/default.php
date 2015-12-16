<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

@trigger_error('The resources/config/'.basename(__FILE__).' file is deprecated since version 1.3 and will be removed in 2.0. Include the config file in your own library instead.');

$kernelRootDir = $container->getParameter('kernel.root_dir');
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

$loader->import('dist/parameters.yml');
if (class_exists('Symfony\Bundle\MonologBundle\MonologBundle')) {
    $loader->import('dist/monolog.yml');
}
$loader->import('dist/doctrine.yml');
$loader->import('dist/security.yml');
$loader->import('dist/framework.php');

<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$vendorDir = realpath(__DIR__.'/../../..');

if (!$loader = include $vendorDir.'/autoload.php') {
    $nl = 'cli' === substr(\PHP_SAPI, 0, 3) ? \PHP_EOL : '<br />';
    echo $nl.$nl.
        'You must set up the project dependencies.'.$nl.
        'Run the following commands in '.dirname($vendorDir).':'.$nl.$nl.
        'curl -s http://getcomposer.org/installer | php'.$nl.
        'php composer.phar install'.$nl;
    exit(1);
}

if (!defined('CMF_TEST_ROOT_DIR')) {
    define('CMF_TEST_ROOT_DIR', realpath(__DIR__.'/..'));
}
if (!defined('CMF_TEST_CONFIG_DIR')) {
    define('CMF_TEST_CONFIG_DIR', CMF_TEST_ROOT_DIR.'/resources/config');
}

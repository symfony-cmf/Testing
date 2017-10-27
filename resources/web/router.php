<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$req = $_SERVER['REQUEST_URI'];
$asset = preg_replace('/\?.*/', '', __DIR__.$req);

// If this looks like a file request then return
// false and serve the file
if ($asset !== __DIR__.'/' && file_exists($asset)) {
    return false;
}

// otherwise lets run the application
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __DIR__.DIRECTORY_SEPARATOR.'app_test.php';

require 'app_test.php';

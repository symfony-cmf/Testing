<?php

$req = $_SERVER['REQUEST_URI'];
$asset = preg_replace('/\?.*/', '', __DIR__.$req);

// If this looks like a file request then return
// false and serve the file
if ($asset != __DIR__.'/' && file_exists($asset)) {
    return false;
}

// otherwise lets run the application
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __DIR__.DIRECTORY_SEPARATOR.'app_test.php';

require 'app_test.php';

<?php

$req = $_SERVER['REQUEST_URI'];

// If this looks like a file request then return
// false and serve the file
if (preg_match('^\.[A-Za-z]{3,4}$^', $req)) {
    return false;
}

// otherwise lets run the application
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __DIR__.DIRECTORY_SEPARATOR.'app_test.php';

require 'app_test.php';

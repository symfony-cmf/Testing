<?php

use Symfony\Component\Filesystem\Filesystem;

$cwd = getcwd();

$vendorDir = sprintf('%s/vendor', $cwd);
$skeletDir = sprintf('%s/symfony-cmf/testing/skeleton', $vendorDir);
$testDir = sprintf('%s/Tests/Functional', $cwd);

if (!file_exists($vendorDir)) {
    echo(sprintf(
        '"vendor" directory does not exist in "%s". You must '.
        'install run composer install --dev'
    , $cwd));
    exit(1);
}

require $vendorDir.'/autoload.php';

$fs = new Filesystem;
$fs->mkdir($testDir.'/app');
$fs->mirror($skeletDir.'/app', $testDir.'/app');

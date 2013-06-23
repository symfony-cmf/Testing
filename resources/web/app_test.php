<?php

use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

$kernel = include __DIR__.'/../../bootstrap/kernel_bootstrap.php';
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

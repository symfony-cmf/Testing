<?php

use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once __DIR__.'/../../bootstrap/bootstrap.php';

$request = Request::createFromGlobals();
$env = $request->query->get('env', 'phpcr');
$request->query->remove('env');

$kernel = include __DIR__.'/../../bootstrap/kernel_bootstrap.php';
$kernel->loadClassCache();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../../bootstrap/bootstrap.php';

$request = Request::createFromGlobals();
$env = $request->query->get('env', 'phpcr');
$request->query->remove('env');

$kernel = include __DIR__.'/../../bootstrap/kernel_bootstrap.php';
$kernel->loadClassCache();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

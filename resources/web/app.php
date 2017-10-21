<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../../bootstrap/bootstrap.php';

Debug::enable();

$request = Request::createFromGlobals();
$env = $request->query->get('env', 'phpcr');
$request->query->remove('env');

$kernel = include __DIR__.'/../../bootstrap/kernel_bootstrap.php';
$kernel->loadClassCache();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

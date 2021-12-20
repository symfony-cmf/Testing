<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$routerPath = '%kernel.root_dir%/config/routing.php';
if ($container->hasParameter('kernel.project_dir')) {
    $routerPath = '%kernel.project_dir%/config/routing.php';
}

$config = [
    'secret' => 'test',
    'test' => null,
    'form' => true,
    'validation' => [
        'enabled' => true,
        'enable_annotations' => true,
    ],
    'router' => [
        'resource' => $routerPath,
    ],
    'default_locale' => 'en',
    'translator' => [
        'fallback' => 'en',
    ],
];

if (interface_exists(\Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface::class)) {
    // Symfony 5.3+
    $config = array_merge($config, ['session' => ['storage_factory_id' => 'session.storage.factory.mock_file']]);
} else {
    // Symfony <5.3
    $config = array_merge($config, ['session' => ['storage_id' => 'session.storage.filesystem']]);
}

$container->loadFromExtension('framework', $config);

$container->loadFromExtension('twig', [
    'debug' => '%kernel.debug%',
    'strict_variables' => '%kernel.debug%',
]);

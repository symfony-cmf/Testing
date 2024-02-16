<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$config = [
    'secret' => 'test',
    'test' => null,
    'form' => true,
    'validation' => [
        'enabled' => true,
    ],
    'router' => [
        'resource' => '%kernel.project_dir%/config/routing.php',
    ],
    'default_locale' => 'en',
    'translator' => [
        'fallback' => 'en',
    ],
    'session' => [
        'storage_factory_id' => 'session.storage.factory.mock_file',
    ],
];

$container->loadFromExtension('framework', $config);

$container->loadFromExtension('twig', [
    'debug' => '%kernel.debug%',
    'strict_variables' => '%kernel.debug%',
]);

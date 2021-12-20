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
    'encoders' => [
        'Symfony\Component\Security\Core\User\User' => 'plaintext',
    ],
    'role_hierarchy' => [
        'ROLE_ADMIN' => 'ROLE_USER',
        'ROLE_SUPER_ADMIN' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
    ],
    'providers' => [
        'in_memory' => [
            'memory' => [
                'users' => [
                    'admin' => ['password' => 'adminpass', 'roles' => ['ROLE_ADMIN']],
                ],
            ],
        ],
    ],
    'firewalls' => [
        'dev' => [
            'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
            'security' => false,
        ],
        'main' => [
            'pattern' => '^/',
            'http_basic' => [
                'realm' => 'Secured Demo Area',
            ],
        ],
    ],
];

if (class_exists(\Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider::class)) {
    // Symfony <=5.4
    $config = array_merge($config, ['firewall' => ['main' => ['anonymous' => null]]]);
}

$container->loadFromExtension('security', $config);

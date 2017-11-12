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
    'session' => [
        'backend' => '%phpcr_backend%',
        'workspace' => '%phpcr_workspace%',
        'username' => '%phpcr_user%',
        'password' => '%phpcr_pass%',
    ],
    'odm' => [
        'auto_mapping' => true,
        'auto_generate_proxy_classes' => '%kernel.debug%',
        'locales' => [
            'en' => ['de', 'fr'],
            'de' => ['en', 'fr'],
            'fr' => ['en', 'de'],
        ],
    ],
];

$kernelRootDir = $container->getParameter('kernel.root_dir');
$bundleFQN = $container->getParameter('cmf_testing.bundle_fqn');
if (getenv('KERNEL_CLASS')) {
    $phpcrOdmDocDir = sprintf('%s/Document', $kernelRootDir);
    $phpcrOdmDocPrefix = sprintf('%s\Tests\Fixtures\App\Document', $bundleFQN);
} else {
    $phpcrOdmDocDir = sprintf('%s/../Document', $kernelRootDir);
    $phpcrOdmDocPrefix = sprintf('%s\Tests\Resources\Document', $bundleFQN);
}

if (file_exists($phpcrOdmDocDir)) {
    $config['odm']['mappings']['test_additional'] = [
        'type' => 'annotation',
        'prefix' => $phpcrOdmDocPrefix,
        'dir' => $phpcrOdmDocDir,
        'is_bundle' => false,
    ];
}

$container->loadFromExtension('doctrine_phpcr', $config);

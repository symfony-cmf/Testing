<?php

$config = array(
    'session' => array(
        'backend' =>  '%phpcr_backend%',
        'workspace' => '%phpcr_workspace%',
        'username' => '%phpcr_user%',
        'password' => '%phpcr_pass%',
    ),
    'odm' => array(
        'auto_mapping' => true,
        'auto_generate_proxy_classes' => '%kernel.debug%',
        'locales' => array(
            'en' => array('de', 'fr'),
            'de' => array('en', 'fr'),
            'fr' => array('en', 'de'),
        ),
    ),
);

$kernelRootDir = $container->getParameter('kernel.root_dir');
$bundleFQN = $container->getParameter('cmf_testing.bundle_fqn');
$phpcrOdmDocDir = sprintf('%s/../Document', $kernelRootDir);
$phpcrOdmDocPrefix = sprintf('%s\Tests\Functional\App\Document', $bundleFQN);

if (file_exists($phpcrOdmDocDir)) {
    $config['odm']['mappings'] = array(
        'test' => array(
            'type' => 'annotation',
            'prefix' => $phpcrOdmDocPrefix,
            'dir' => $phpcrOdmDocDir,
            'is_bundle' => false,
        ),
    );
}

$container->loadFromExtension('doctrine_phpcr', $config);

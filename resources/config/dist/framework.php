<?php

$config = array(
    'secret' => 'test',
    'test' => null,
    'session' => array(
        'storage_id' => 'session.storage.filesystem',
    ),
    'form' => true,
    'validation' => array(
        'enabled' => true,
        'enable_annotations' => true,
    ),
    'router' => array(
        'resource' => '%kernel.root_dir%/config/routing.php',
    ),
    'default_locale' => 'en',
    'templating' => array(
        'engines' => array('twig'),
    ),
    'translator' => array(
        'fallback' => 'en',
    ),
);

if (Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 20800) {
    $config['form'] = array(
        'csrf_protection' => true
    );
} else {
    $config['csrf_protection'] = true;
}

$container->loadFromExtension('framework', $config);

$container->loadFromExtension('twig', array(
    'debug' => '%kernel.debug%',
    'strict_variables' => '%kernel.debug%',
));

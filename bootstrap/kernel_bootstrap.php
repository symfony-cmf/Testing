<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (getenv('KERNEL_CLASS')) {
    $kernelClassName = getenv('KERNEL_CLASS');

    return new $kernelClassName($env, true);
}
$rootDir = realpath(__DIR__.'/../../../..');
$phpUnitFile = $rootDir.'/phpunit.xml.dist';

if (!file_exists($phpUnitFile)) {
    throw new \Exception(sprintf(
        'Cannot find phpunit.xml.dist file in "%s"',
        $phpUnitFile
    ));
}

$xml = new \SimpleXMLElement(file_get_contents($phpUnitFile));

$envClass = $xml->xpath("//php/env[@name='KERNEL_CLASS']");
if (count($envClass)) {
    $kernelClass = (string) $envClass[0]['value'];
} else {
    $envDir = $xml->xpath("//php/server[@name='KERNEL_DIR']");
    if (!count($envDir)) {
        throw new \Exception(
            'KERNEL_CLASS must be set via <env name"KERNEL_CLASS" value="..."/>'
        );
    }
    $kernelClass = 'AppKernel';
    $kernelFile = $rootDir.'/'.$envDir[0]['value'].'/'.$kernelClass.'.php';

    if (!file_exists($kernelFile)) {
        throw new \Exception(sprintf(
            'Cannot find kernel file "%s"',
            $kernelFile
        ));
    }

    require_once $kernelFile;
}

return new $kernelClass($env, true);

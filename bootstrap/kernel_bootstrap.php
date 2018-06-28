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

$envDir = $xml->xpath("//php/server[@name='KERNEL_DIR']");
if (!count($envDir)) {
    throw new \Exception(
        'Kernel path must be set via <server name"KERNEL_DIR" value="..."/>'
    );
}
$envClass = $xml->xpath("//php/env[@name='KERNEL_CLASS']");

$kernelClass = null === $envClass ? 'AppKernel' : (string) $envClass[0]['value'];
$kernelNs = explode('\\', $kernelClass);
$kernelFile = $rootDir.'/'.$envDir[0]['value'].'/'.array_pop($kernelNs).'.php';

if (!file_exists($kernelFile)) {
    throw new \Exception(sprintf(
        'Cannot find kernel file "%s"',
        $kernelFile
    ));
}

require_once $kernelFile;

return new $kernelClass($env, true);

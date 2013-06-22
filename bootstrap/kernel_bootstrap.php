<?php
$rootDir = realpath(__DIR__.'/../../../..');
$vendorDir = realpath($rootDir.'/vendor');
$phpUnitFile = $rootDir.'/phpunit.xml.dist';

if (!file_exists($phpUnitFile)) {
    throw new \Exception(sprintf(
        'Cannot find phpunit.xml.dist file in "%s"',
        $phpUnitFile
    ));
}

$xml = new \SimpleXMLElement(file_get_contents($phpUnitFile));
$kernelDir = $xml->php[0]->server[0]['value'];

$kernelFile = $rootDir.'/'.$kernelDir.'/AppKernel.php';

if (!file_exists($kernelFile)) {
    throw new \Exception(sprintf(
        'Cannot find kernel file "%s"',
        $kernelFile
    ));
}

require_once $vendorDir.'/symfony-cmf/testing/bootstrap/bootstrap.php';
require_once $kernelFile;

return new AppKernel('test', true);

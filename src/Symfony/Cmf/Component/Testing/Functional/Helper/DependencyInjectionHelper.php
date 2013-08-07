<?php

namespace Symfony\Cmf\Component\Testing\Functional\Helper;

class DependencyInjectionHelper
{
    public function validate($xmlFile, $schemaFile)
    {
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $configEls = $dom->getElementsByTagName('config');

        if (count($configEls) != 1) {
            throw new \Exception(sprintf(
                'Could not find config element in file "%s"',
                $xmlFile
            ));
        }

        $newDom = new \DomDocument();
        $newDom->appendChild($newDom->importNode($configEls->item(0), true));

        return $newDom->schemaValidate($schemaFile);
    }
}

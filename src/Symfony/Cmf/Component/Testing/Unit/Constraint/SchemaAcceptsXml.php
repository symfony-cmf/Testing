<?php

namespace Symfony\Cmf\Component\Testing\Unit\Constraint;

class SchemaAcceptsXml extends \PHPUnit_Framework_Constraint
{
    protected $schemaFile;

    public function __construct($schemaFile)
    {
        $this->schemaFile = $schemaFile;
    }

    public function matches($others)
    {
        foreach ($others as $other) {
            $configElement = $other->getElementsByTagName('config');

            if (1 !== count($configElement)) {
                throw new \InvalidArgumentException('Can only test a file if it contains 1 <config> elements, %d given', count($configElement));
            }

            $configDom = new \DomDocument();
            $configDom->appendChild($configDom->importNode($configElement->item(0), true));

            if (!$configDom->schemaValidate($this->schemaFile)) {
                return false;
            }
        }

        return true;
    }

    public function toString()
    {
        return sprintf('is accepted by the XML schema "%s"', $this->schemaFile);
    }
}

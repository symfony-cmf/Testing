<?php

namespace Symfony\Cmf\Component\Testing\Unit\Constraint;

class SchemaAcceptsXml extends \PHPUnit_Framework_Constraint
{
    protected $xml;
    protected $failingElement;
    protected $errors;

    public function __construct($xml)
    {
        $this->xml = $xml;
    }

    public function matches($schemaFile)
    {
        foreach ($this->xml as $id => $dom) {
            $configElement = $dom->getElementsByTagName('config');

            if (1 !== $configElement->length) {
                throw new \InvalidArgumentException(sprintf('Can only test a file if it contains 1 <config> element, %d given', $configElement->length));
            }

            $configDom = new \DomDocument();
            $configDom->appendChild($configDom->importNode($configElement->item(0), true));

            libxml_use_internal_errors(true);
            if (!$configDom->schemaValidate($schemaFile)) {
                $this->errors = libxml_get_errors();
                $this->failingElement = $id;

                return false;
            }
        }

        return true;
    }

    public function count()
    {
        return count($this->xml);
    }

    public function toString() { }

    protected function failureDescription($schemaFile)
    {
        return sprintf(
            '"%s" is accepted by the XML schema "%s"',
            \PHPUnit_Util_Type::export($this->xml[$this->failingElement]),
            $schemaFile
        );
    }

    protected function additionalFailureDescription($schema)
    {
        $str = '';

        foreach ($this->errors as $error) {
            $str .= $error->message.($error->file ? ' in'.$error->file : '').' on line '.$error->line."\n";
        }

        return $str;
    }
}

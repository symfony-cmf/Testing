<?php

namespace Symfony\Cmf\Component\Testing\Unit\Constraint;

class SchemaAcceptsXml extends \PHPUnit_Framework_Constraint
{
    protected $schemaFile;
    protected $failingElement;
    protected $errors;

    public function __construct($schemaFile)
    {
        $this->schemaFile = $schemaFile;
    }

    public function matches($others)
    {
        foreach ($others as $id => $other) {
            $configElement = $other->getElementsByTagName('config');

            if (1 !== $configElement->length) {
                throw new \InvalidArgumentException(sprintf('Can only test a file if it contains 1 <config> element, %d given', $configElement->length));
            }

            $configDom = new \DomDocument();
            $configDom->appendChild($configDom->importNode($configElement->item(0), true));

            libxml_use_internal_errors(true);
            if (!$configDom->schemaValidate($this->schemaFile)) {
                $this->errors = libxml_get_errors();
                $this->failingElement = $id;
                return false;
            }
        }

        return true;
    }

    public function toString() { }

    protected function failureDescription($others)
    {
        return sprintf(
            '"%s" is accepted by the XML schema "%s"',
            \PHPUnit_Util_Type::export($others[$this->failingElement]),
            $this->schemaFile
        );
    }

    protected function additionalFailureDescription($other)
    {
        $str = '';

        foreach ($this->errors as $error) {
            $str .= $error->message.($error->file ? ' in'.$error->file : '').' on line '.$error->line."\n";
        }

        return $str;
    }
}

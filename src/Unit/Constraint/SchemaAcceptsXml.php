<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Unit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class SchemaAcceptsXml extends Constraint
{
    protected $xml;

    protected $failingElement;

    protected $errors;

    public function __construct($xml)
    {
        $this->xml = $xml;
    }

    public function matches($schemaFile): bool
    {
        foreach ($this->xml as $id => $dom) {
            $configElement = $dom->getElementsByTagName('config');

            if (1 !== $configElement->length) {
                throw new \InvalidArgumentException(sprintf('Can only test a file if it contains 1 <config> element, %d given', $configElement->length));
            }

            $configDom = new \DOMDocument();
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

    public function count(): int
    {
        return \count($this->xml);
    }

    public function toString(): string
    {
    }

    protected function failureDescription($schemaFile): string
    {
        return sprintf(
            'Xml is accepted by the XML schema "%s"',
            $schemaFile
        );
    }

    protected function additionalFailureDescription($schema): string
    {
        $str = "\n".$this->xml[$this->failingElement]->saveXml()."\n\n";

        foreach ($this->errors as $error) {
            $error = trim($error->message).($error->file ? ' in'.$error->file : '').' on line '.$error->line."\n";

            // avoid repeating same error
            if (false === strpos($str, $error)) {
                $str .= $error;
            }
        }

        return $str;
    }
}

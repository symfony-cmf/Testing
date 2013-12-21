<?php

namespace Symfony\Cmf\Component\Testing\Unit;

class XmlSchemaTestCase extends \PHPUnit_Framework_TestCase
{
    public static function assertSchemaAcceptsXml($xmlDoms, $schemaPath, $message = '')
    {
        if (!is_array($xmlDoms)) {
            $xmlDoms = array($xmlDoms);
        }

        $xmlDoms = array_map(function ($dom) {
            if (is_string($dom)) {
                $xml = $dom;
                $dom = new \DOMDocument();
                $dom->load($xml);

                return $dom;
            }
            
            if (!$dom instanceof DOMDocument) {
                throw new \InvalidArgumentException('The first argument of assertSchemaAcceptsXml should be instances of \DOMDocument, "%s" given', get_class($xml));
            }
        }, $xmlDoms);

        return self::assertThat($xmlDoms, new Constraint\SchemaAcceptsXml($schemaPath), $message);
    }
}

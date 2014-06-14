<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Testing\Unit;

abstract class XmlSchemaTestCase extends \PHPUnit_Framework_TestCase
{
    public static function assertSchemaAcceptsXml($xmlDoms, $schemaPath, $message = '')
    {
        return self::assertThat($schemaPath, self::getSchemaAcceptsXmlConstraint($xmlDoms), $message);
    }

    public static function assertSchemaRefusesXml($xmlDoms, $schemaPath, $message = '')
    {
        return self::assertThat(
            $schemaPath,
            new \PHPUnit_Framework_Constraint_Not(self::getSchemaAcceptsXmlConstraint($xmlDoms)),
            $message
        );
    }

    private static function getSchemaAcceptsXmlConstraint($xmlDoms)
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
            
            if (!$dom instanceof \DOMDocument) {
                throw new \InvalidArgumentException(sprintf('The first argument of assertSchemaAcceptsXml should be instances of \DOMDocument, "%s" given', get_class($dom)));
            }

            return $dom;
        }, $xmlDoms);

        return new Constraint\SchemaAcceptsXml($xmlDoms);
    }
}

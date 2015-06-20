<?php

namespace Symfony\Cmf\Component\Testing\Extension\Phpunit;

use Symfony\Cmf\Component\Testing\Assert as BaseAssert;
use Symfony\Cmf\Component\Testing\Exception\AssertionFailedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * The PHPunit adapter for the base Assert class.
 *
 * It makes sure PHPunit fails normally when using Assert.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Assert extends BaseAssert
{
    public static function responseOk(Response $response)
    {
        try {
            parent::responseOk($response);
        } catch (AssertionFailedException $e) {
            throw new \PHPUnit_Framework_ExpectationFailedException($e->getMessage());
        }
    }

    public static function schemaAcceptsXml($xmlDoms, $schemaPath, $message = '')
    {
        \PHPUnit_Framework_Assert::assertThat($schemaPath, self::getSchemaAcceptsXmlConstraint($xmlDoms), $message);
    }

    public static function schemaRefusesXml($xmlDoms, $schemaPath, $message = '')
    {
        \PHPUnit_Framework_Assert::assertThat(
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

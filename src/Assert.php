<?php

namespace Symfony\Cmf\Component\Testing;

use Symfony\Cmf\Component\Testing\Exception\AssertionFailedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * A class with extra usefull test assertions.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Assert
{
    public static function responseOk(Response $response)
    {
        if (200 === $response->getStatusCode()) {
            return;
        }

        // extract exception message from output (assuming the output is the default Symfony exception page)
        $previousUseErrors = libxml_use_internal_errors(true);

        $dom = new \DomDocument();
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXpath($dom);
        $exceptionElement = $xpath->query('//div[contains(@class,"text-exception")]/h1');
        $exceptionMessage = $exceptionElement->length ? trim($exceptionElement->item(0)->nodeValue) : false;

        libxml_use_internal_errors($previousUseErrors);

        throw new AssertionFailedException(sprintf(
            'Status code for response was %d instead of the expected 200.%s',
            $response->getStatusCode(),
            $exceptionMessage ? ' Exception message: "'.$exceptionMessage.'"' : ''
        ));
    }
}

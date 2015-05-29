<?php

namespace Symfony\Cmf\Component\Testing;

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
            return true;
        }

        // extract exception message from output (assuming the output is the default Symfony exception page)
        libxml_use_internal_errors(true);

        $dom = new \DomDocument();
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXpath($dom);
        $exceptionElement = $xpath->query('//div[contains(@class,"text-exception")]/h1');

        throw new \LogicException(sprintf(
            'Status code for response was %d instead of the expected 200.%s',
            $response->getStatusCode(),
            $exceptionElement->length ? ' Exception message: "'.trim($exceptionElement->item(0)->nodeValue).'"."' : null
        ));
    }
}

<?php

namespace Symfony\Cmf\Component\Testing\Extension\Phpunit;

use Symfony\Cmf\Component\Testing\Assert as BaseAssert;
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
        } catch (\LogicException $e) {
            throw new \PHPUnit_Framework_ExpectationFailedException($e->getMessage());
        }
    }
}

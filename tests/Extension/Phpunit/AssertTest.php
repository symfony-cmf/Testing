<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Extension\Phpunit;

use Symfony\Cmf\Component\Testing\Extension\Phpunit\Assert;

class AssertTest extends \PHPUnit_Framework_TestCase
{
    public function testSchemaAcceptsXmlWithSingleDom()
    {
        $dom = new \DomDocument();
        $dom->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" required="f"/></container>');

        Assert::schemaAcceptsXml($dom, __DIR__.'/../Fixtures/schema/schema1.xsd');
    }

    public function testSchemaRefusesXml()
    {
        $dom = new \DomDocument();
        $dom->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" /></container>');

        Assert::schemaRefusesXml($dom, __DIR__.'/../Fixtures/schema/schema1.xsd');
    }
}

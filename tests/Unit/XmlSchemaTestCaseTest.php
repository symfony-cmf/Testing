<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Unit;

use Symfony\Cmf\Component\Testing\Unit\XmlSchemaTestCase;

class XmlSchemaTestCaseTest extends XmlSchemaTestCase
{
    public function testAcceptsSingleDomsWithoutArray()
    {
        $dom = new \DomDocument();
        $dom->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" required="f"/></container>');
        $this->assertSchemaAcceptsXml($dom, __DIR__.'/../Fixtures/schema/schema1.xsd');
    }

    public function testNegativeAssertion()
    {
        $dom = new \DomDocument();
        $dom->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" /></container>');

        $this->assertSchemaRefusesXml($dom, __DIR__.'/../Fixtures/schema/schema1.xsd');
    }
}

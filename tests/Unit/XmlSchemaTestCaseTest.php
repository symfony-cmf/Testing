<?php

namespace Tests\Unit;

use Symfony\Cmf\Component\Testing\Unit\XmlSchemaTestCase;

class XmlSchemaTestCaseTest extends XmlSchemaTestCase
{
    public function testAcceptsSingleDomsWithoutArray()
    {
        $dom = new \DomDocument();
        $dom->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" required="f"/></container>');
        $this->assertSchemaAcceptsXml($dom, __DIR__.'/../Fixtures/schema/schema1.xsd');
    }
}

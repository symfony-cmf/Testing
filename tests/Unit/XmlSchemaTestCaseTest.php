<?php

namespace Tests\Unit;

use Symfony\Cmf\Component\Testing\Unit\XmlSchemaTestCase;

class XmlSchemaTestCaseTest extends XmlSchemaTestCase
{
    /**
     * @dataProvider getAssertingData
     */
    public function testAsserting($input, $schemaFile, $result, $message = null)
    {
        $failed = false;

        try {
            $this->assertSchemaAcceptsXml($input, $schemaFile);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $failed = true;
        }

        if ($failed) {
            $this->assertFalse($result, 'schema should accept xml');
        } else {
            $this->assertTrue($result, 'schema should not accept xml');
            if ($message) {
                $this->assertEquals($message, $e->getMessage());
            }
        }
    }

    public function getAssertingData()
    {
        $schema1 = __DIR__.'/../Fixtures/schema/schema1.xsd';

        $data = array();

        $dom1 = new \DomDocument();
        $dom1->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" required="f"/></container>');
        $data[] = array($dom1, $schema1, true);

        $dom2 = new \DomDocument();
        $dom2->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" /></container>');
        $data[] = array($dom2, $schema1, false);

        $data[] = array(array($dom1, $dom1), $schema1, true);
        $data[] = array(array($dom1, $dom2), $schema1, false);
        $data[] = array(array($dom2, $dom1), $schema1, false);

        return $data;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailsIfNoConfigElementIsAvailable()
    {
        $dom = new \DomDocument();
        $dom->loadXML('<container></container>');

        $this->assertSchemaAcceptsXml($dom, __DIR__.'/../Fixtures/schema/schema1.xsd');
    }
}

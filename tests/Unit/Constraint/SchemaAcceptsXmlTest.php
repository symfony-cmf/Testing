<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Unit\Constraint;

use Symfony\Cmf\Component\Testing\Unit\Constraint\SchemaAcceptsXml;

class SchemaAcceptsXmlTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $constraint = new SchemaAcceptsXml(array('config1', 'config2', 'config3'));

        try {
            $this->matches('schema_file.xsd');
        } catch (\Exception $e) {
        }

        $this->assertCount(3, $constraint);
    }

    /**
     * @dataProvider getAssertingData
     */
    public function testAsserting($input, $schemaFile, $result, $message = null)
    {
        $constraint = new SchemaAcceptsXml($input);

        $return = $constraint->matches($schemaFile);

        if ($result) {
            $this->assertTrue($return, 'schema should accept xml');
        } else {
            $this->assertFalse($return, 'schema should not accept xml');
            if ($message) {
                $this->assertEquals($message, $e->getMessage());
            }
        }
    }

    public function getAssertingData()
    {
        $schema1 = __DIR__.'/../../Fixtures/schema/schema1.xsd';

        $data = array();

        $dom1 = new \DomDocument();
        $dom1->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" required="f"/></container>');
        $data[] = array(array($dom1), $schema1, true);

        $dom2 = new \DomDocument();
        $dom2->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" /></container>');
        $data[] = array(array($dom2), $schema1, false);

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

        $constraint = new SchemaAcceptsXml(array($dom));
        $constraint->matches(__DIR__.'/../Fixtures/schema/schema1.xsd');
    }
}

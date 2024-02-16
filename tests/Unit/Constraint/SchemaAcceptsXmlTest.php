<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Testing\Unit\Constraint\SchemaAcceptsXml;

class SchemaAcceptsXmlTest extends TestCase
{
    public function testCount()
    {
        $constraint = new SchemaAcceptsXml(['config1', 'config2', 'config3']);

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

        $data = [];

        $dom1 = new \DOMDocument();
        $dom1->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" required="f"/></container>');
        $data[] = [[$dom1], $schema1, true];

        $dom2 = new \DOMDocument();
        $dom2->loadXML('<container><config xmlns="http://cmf.symfony.com/schema/dic/foo" /></container>');
        $data[] = [[$dom2], $schema1, false];

        $data[] = [[$dom1, $dom1], $schema1, true];
        $data[] = [[$dom1, $dom2], $schema1, false];
        $data[] = [[$dom2, $dom1], $schema1, false];

        return $data;
    }

    public function testFailsIfNoConfigElementIsAvailable()
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<container></container>');

        $constraint = new SchemaAcceptsXml([$dom]);
        $this->expectException(\InvalidArgumentException::class);
        $constraint->matches(__DIR__.'/../Fixtures/schema/schema1.xsd');
    }
}

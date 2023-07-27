<?php

namespace Seat\Tests\Services\ReportParser\Elements;

use ErrorException;
use PHPUnit\Framework\TestCase;
use Seat\Services\ReportParser\Elements\Element;

/**
 * Class ElementTest.
 */
class ElementTest extends TestCase
{
    public function testAdd()
    {
        $element = new Element(['value1']);
        $element->add('value2');

        $this->assertContains('value2', $element->fields());
    }

    public function testRemove()
    {
        $element = new Element(['value1']);
        $element->add('value2');
        $element->remove('value1');

        $this->assertContains('value2', $element->fields());
        $this->assertNotContains('value1', $element->fields());
    }

    public function testIsEmpty()
    {
        $element = new Element([]);

        $this->assertTrue($element->isEmpty());
    }

    public function testGetter()
    {
        $element = new Element([
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
        ]);

        $this->assertEquals('value2', $element->field2);
    }

    public function testFields()
    {
        $artifact = [
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
        ];

        $element = new Element($artifact);

        $this->assertEquals($artifact, $element->fields());
    }

    public function testUndefinedFieldException()
    {
        $element = new Element([
            'field1' => 'value1',
            'field3' => 'value3',
        ]);

        $this->expectException(ErrorException::class);
        $element->field2;
    }
}

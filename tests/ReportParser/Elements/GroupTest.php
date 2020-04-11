<?php

use PHPUnit\Framework\TestCase;
use Seat\Services\ReportParser\Elements\Element;
use Seat\Services\ReportParser\Elements\Group;

class GroupTest extends TestCase
{

    public function testAdd()
    {
        $group = new Group('name');
        $element = new Element(['value1', 'value2']);

        $group->add($element);

        $this->assertContains($element, $group->getElements());
    }

    public function testIsEmpty()
    {
        $group = new Group('name');
        $element = new Element(['value1', 'value2']);

        $this->assertTrue($group->isEmpty());

        $group->add($element);

        $this->assertFalse($group->isEmpty());
    }

    public function testGetName()
    {
        $group = new Group('name');

        $this->assertEquals('name', $group->getName());
    }

    public function testGetElements()
    {
        $group = new Group('name');
        $group->add(new Element(['value1', 'value2']));
        $group->add(new Element(['value3', 'value4']));

        $this->assertContainsOnlyInstancesOf(Element::class, $group->getElements());
    }

    public function testRemove()
    {
        $group = new Group('name');

        $element1 = new Element(['value1', 'value2']);
        $element2 = new Element(['value3', 'value4']);

        $group->add($element1);
        $this->assertContains($element1, $group->getElements());

        $group->add($element2);
        $group->remove($element1);

        $this->assertContains($element2, $group->getElements());
        $this->assertNotContains($element1, $group->getElements());
    }
}

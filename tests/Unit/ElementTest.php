<?php

namespace App\Tests;

use App\Element;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    public function testWithNormalString()
    {
        $element = Element::fromString("Приморский край");
        $this->assertEquals(["Приморский", "край"], $element->getOrdinaryWords());
    }

    public function testWithEmptyString()
    {
        $element = Element::fromString("");
        $this->assertEquals([], $element->getOrdinaryWords());
        $this->assertEquals([], $element->getMinusWords());
    }

    public function testWithNoSpaces()
    {
        $element = Element::fromString("Владивосток");
        $this->assertEquals(["Владивосток"], $element->getOrdinaryWords());
        $this->assertEquals([], $element->getMinusWords());
    }

    public function testWithMultipleSpaces()
    {
        $element = Element::fromString("Приморский    край");
        $this->assertEquals(["Приморский", "край"], $element->getOrdinaryWords());
    }

    public function testWithMinusWords()
    {
        $element = Element::fromString("Приморский край -Владивосток");
        $this->assertEquals(["Приморский", "край"], $element->getOrdinaryWords());
        $this->assertEquals(["-Владивосток"], $element->getMinusWords());
    }

    public function testWithShortWords()
    {
        $element = Element::fromString("с пробегом");
        $this->assertEquals(["+с", "пробегом"], $element->getOrdinaryWords());
    }

    public function testWithValidSymbols()
    {
        $element = Element::fromString("!Владивосток");
        $this->assertEquals(["!Владивосток"], $element->getOrdinaryWords());
    }

    public function testWithInvalidSymbols()
    {
        $element = Element::fromString("CRF-450X");
        $this->assertEquals(["CRF", "450X"], $element->getOrdinaryWords());
    }
}

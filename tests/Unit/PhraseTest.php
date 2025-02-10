<?php

namespace App\Tests;

use App\Phrase;
use PHPUnit\Framework\TestCase;

class PhraseTest extends TestCase
{
    public function testAddMinusWord(): void
    {
        $ordinaryWords = ["Владивосток", "цена", "Honda"];
        $minusWords = [];

        $phrase = new Phrase($ordinaryWords, $minusWords);
        $phrase->addMinusWord("-CRF");

        $this->assertEquals(["-CRF"], $phrase->getMinusWords());
    }
}

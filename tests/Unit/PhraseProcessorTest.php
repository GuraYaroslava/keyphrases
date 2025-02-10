<?php

namespace App\Tests;

use App\Phrase;
use App\PhraseProcessor;
use PHPUnit\Framework\TestCase;

class PhraseProcessorTest extends TestCase
{
    public function testDeduplicate(): void
    {
        $phrases = [
            new Phrase(["A", "B"]),
            new Phrase(["A", "B"]),
            new Phrase(["C", "D"]),
        ];

        $expectedPhrases = [
            new Phrase(["A", "B"]),
            new Phrase(["C", "D"]),
        ];

        list($actualPhrases,) = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $actualPhrases);
    }

    public function testApplyMinusWords(): void
    {
        $phrases = [
            new Phrase(["A", "B"]),
            new Phrase(["A"]),
        ];

        $expectedPhrases = [
            new Phrase(["A", "B"], [], [], []),
            new Phrase(["A"],      [], [], ["-B"]),
        ];

        list($actualPhrases,) = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $actualPhrases);
    }

    public function testProcessWithEmptyArray(): void
    {
        $phrases = [];
        $expectedPhrases = [];
        list($actualPhrases,) = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $actualPhrases);
    }

    public function testProcessWithNoIntersections(): void
    {
        $phrases = [
            new Phrase(["A", "B"]),
            new Phrase(["C", "D"]),
        ];
        $expectedPhrases = [
            new Phrase(["A", "B"]),
            new Phrase(["C", "D"]),
        ];

        list($actualPhrases,) = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $actualPhrases);
    }

    public function testProcessWithMultipleIntersections(): void
    {
        $phrases = [
            new Phrase(["A", "B", "C"]),
            new Phrase(["A", "B"]),
            new Phrase(["A"]),
        ];

        $expectedPhrases = [
            new Phrase(["A", "B", "C"], [], [], []),
            new Phrase(["A", "B"],      [], [], ["-C"]),
            new Phrase(["A"],           [], [], ["-B", "-C"]),
        ];

        list($actualPhrases,) = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $actualPhrases);
    }
}

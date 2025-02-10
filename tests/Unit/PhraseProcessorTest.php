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
            new Phrase(["A", "B"], []),
            new Phrase(["A", "B"], []),
            new Phrase(["C", "D"], []),
        ];

        $expectedPhrases = [
            new Phrase(["A", "B"], []),
            new Phrase(["C", "D"], []),
        ];

        $result = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $result);
    }

    public function testApplyMinusWords(): void
    {
        $phrases = [
            new Phrase(["A", "B"], []),
            new Phrase(["A"], []),
        ];

        $expectedPhrases = [
            new Phrase(["A", "B"], []),
            new Phrase(["A"], ["-B"]),
        ];

        $result = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $result);
    }

    public function testProcessWithEmptyArray(): void
    {
        $phrases = [];
        $expectedPhrases = [];
        $result = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $result);
    }

    public function testProcessWithNoIntersections(): void
    {
        $phrases = [
            new Phrase(["A", "B"], []),
            new Phrase(["C", "D"], []),
        ];
        $expectedPhrases = [
            new Phrase(["A", "B"], []),
            new Phrase(["C", "D"], []),
        ];

        $result = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $result);
    }

    public function testProcessWithMultipleIntersections(): void
    {
        $phrases = [
            new Phrase(["A", "B", "C"], []),
            new Phrase(["A", "B"], []),
            new Phrase(["A"], []),
        ];

        $expectedPhrases = [
            new Phrase(["A", "B", "C"], []),
            new Phrase(["A", "B"], ["-C"]),
            new Phrase(["A"], ["-B", "-C"]),
        ];

        $result = PhraseProcessor::process($phrases);

        $this->assertEquals($expectedPhrases, $result);
    }
}

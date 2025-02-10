<?php

namespace App\Tests;

use App\Element;
use App\Phrase;
use App\PhraseGenerator;
use PHPUnit\Framework\TestCase;

class PhraseGeneratorTest extends TestCase
{
    public function testGenerateWithSingleGroup(): void
    {
        $groups = ["Владивосток"];

        $expectedPhrases = [
            new Phrase(["Владивосток"], []),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }

    public function testGenerateWithMultipleGroups(): void
    {
        $groups = ["AAA, BBB", "CCC, DDD"];

        $expectedPhrases = [
            new Phrase(["AAA", "CCC"], []),
            new Phrase(["AAA", "DDD"], []),
            new Phrase(["BBB", "CCC"], []),
            new Phrase(["BBB", "DDD"], []),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }

    public function testGenerateWithEmptyGroups(): void
    {
        $groups = ["", ""];

        $expectedPhrases = [
            new Phrase([], [], []),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }

    public function testGenerateWithMinusWords(): void
    {
        $groups = ["AAA -BBB", "CCC -DDD"];

        $expectedPhrases = [
            new Phrase(
                ["AAA", "CCC"],
                ["-BBB", "-DDD"]
            ),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }

    public function testGenerateWithShortWords(): void
    {
        $groups = ["AAA -BBB", "C -DDD"];

        $expectedPhrases = [
            new Phrase(
                ["AAA", "+C"],
                ["-BBB", "-DDD"]
            ),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }

    public function testGenerateWithInvalidSymbols(): void
    {
        $groups = ["AAA -BBB", "C DDD-ddd"];

        $expectedPhrases = [
            new Phrase(
                ["AAA", "+C", "DDD", "ddd"],
                ["-BBB"]
            ),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }

    public function testGenerateWithMixedGroups(): void
    {
        $groups = ["AAA, B", "CCC, DDD-ddd -CCC"];

        $expectedPhrases = [
            new Phrase(["AAA", "CCC"], []),
            new Phrase(["AAA", "DDD", "ddd"], ["-CCC"]),
            new Phrase(["+B", "CCC"], []),
            new Phrase(["+B", "DDD", "ddd"], ["-CCC"]),
        ];

        $phrases = PhraseGenerator::generate($groups);

        $this->assertEquals($expectedPhrases, $phrases);
    }
}

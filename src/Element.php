<?php

namespace App;

class Element
{
    private $ordinaryWords = [];

    private $minusWords = [];

    private $maxDisplayOrdinaryWordsNumber = 0;

    public function getMaxDisplayOrdinaryWordsNumber(): int
    {
        return $this->maxDisplayOrdinaryWordsNumber;
    }

    public function setMaxDisplayOrdinaryWordsNumber($value): void
    {
        $this->maxDisplayOrdinaryWordsNumber = $value;
    }

    public function getOrdinaryWords(): array
    {
        return $this->ordinaryWords;
    }

    public function getMinusWords(): array
    {
        return $this->minusWords;
    }

    public static function fromString(string $element): self
    {
        $instance = new self();
        $processedWords = [];
        $words = preg_split('/\s+/', trim($element));
        self::processWords($words, $processedWords);
        foreach ($processedWords as $word) {
            if ($word === '') {
                continue;
            }

            $isMinusWord = strpos($word, '-') === 0;
            if ($isMinusWord) {
                $instance->minusWords[] = $word;
            } else {
                $instance->ordinaryWords[] = $word;
            }
        }

        return $instance;
    }

    private static function processWords(array $words, array &$processedWords): void
    {
        foreach ($words as $word) {
            $correctWord = self::wordCorrect($word);
            if ($correctWord === '') {
                continue;
            }
            $subWords = preg_split('/\s+/', $correctWord);
            if (count($subWords) > 1) {
                self::processWords($subWords, $processedWords);
            } else {
                $wordHead = mb_substr($correctWord, 0, 1);
                if (mb_strlen($correctWord) <= 2 && $wordHead !== '+') {
                    $correctWord = '+' . $correctWord;
                }

                $processedWords[] = $correctWord;
            }
        }
    }

    private static function wordCorrect(string $word): string
    {
        $correctedWord = '';
        $wordHead = mb_substr($word, 0, 1);
        $wordBody = mb_substr($word, 1);

        if ($wordHead === '-' || $wordHead === '+' || $wordHead === '!') {
            $correctedWord = $wordHead . preg_replace('/[^\p{L}\p{N}]/u', ' ', $wordBody);
        } else {
            $correctedWord = preg_replace('/[^\p{L}\p{N}]/u', ' ', $word);
        }

        return trim($correctedWord);
    }
}

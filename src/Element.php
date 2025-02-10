<?php

namespace App;

/**
 * Элемент группы, ключевое словочетание в строке входных данных
 */
class Element
{
    /**
     * Обычные слова
     * @var array
     */
    private $ordinaryWords = [];

    /**
     * Минус-слова
     * @var array
     */
    private $minusWords = [];

    public function getOrdinaryWords(): array
    {
        return $this->ordinaryWords;
    }

    public function getMinusWords(): array
    {
        return $this->minusWords;
    }

    /**
     * Создать сущность элемента группы из строки
     * @param string $element
     * @return Element
     */
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

    /**
     * Обработать все слова элемента группы
     * @param array $words
     * @param array $result
     * @return void
     */
    private static function processWords(array $words, array &$result): void
    {
        foreach ($words as $word) {
            $correctWord = self::wordCorrect($word);
            if ($correctWord === '') {
                continue;
            }
            $subWords = preg_split('/\s+/', $correctWord);
            if (count($subWords) > 1) {
                self::processWords($subWords, $result);
            } else {
                $wordHead = mb_substr($correctWord, 0, 1);
                if (mb_strlen($correctWord) <= 2 && $wordHead !== '+') {
                    $correctWord = '+' . $correctWord;
                }

                $result[] = $correctWord;
            }
        }
    }

    /**
     * Корректировать слово элемента группы
     * @param string $word
     * @return string
     */
    private static function wordCorrect(string $word): string
    {
        $result = '';
        $wordHead = mb_substr($word, 0, 1);
        $wordBody = mb_substr($word, 1);

        if ($wordHead === '-' || $wordHead === '+' || $wordHead === '!') {
            $result = $wordHead . preg_replace('/[^\p{L}\p{N}]/u', ' ', $wordBody);
        } else {
            $result = preg_replace('/[^\p{L}\p{N}]/u', ' ', $word);
        }

        return trim($result);
    }
}

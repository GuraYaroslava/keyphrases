<?php

namespace App;

class Phrase
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

    public function __construct(array $ordinaryWords, array $minusWords)
    {
        $this->ordinaryWords = $ordinaryWords;
        $this->minusWords = $minusWords;
    }

    /**
     * Сгенерировать ключ по словам в фразе
     * @return string
     */
    public function getKey(): string
    {
        sort($this->ordinaryWords);
        sort($this->minusWords);

        $ordinarySubKey = join(" ", $this->ordinaryWords);
        $minusSubKey = join(" ", $this->minusWords);

        return $ordinarySubKey . " " . $minusSubKey;
    }

    public function toArray(): array
    {
        return array_merge($this->ordinaryWords, $this->minusWords);
    }

    public function __toString(): string
    {
        $parts = array_merge($this->ordinaryWords, $this->minusWords);

        return join(' ', $parts);
    }

    public function getOrdinaryWords(): array
    {
        return $this->ordinaryWords;
    }

    public function getMinusWords(): array
    {
        return $this->minusWords;
    }

    public function addMinusWord($word)
    {
        $this->minusWords[] = $word;
    }
}

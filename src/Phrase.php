<?php

namespace App;

class Phrase
{
    private $ordinaryWords = [];

    private $displayOrdinaryWords = [];

    private $minusWords = [];

    private $additionalMinusWords = [];

    public function __construct(array $ordinaryWords = [], array $minusWords = [], array $displayOrdinaryWords = [], array $additionalMinusWords = [])
    {
        $this->ordinaryWords = $ordinaryWords;
        $this->minusWords = $minusWords;
        $this->displayOrdinaryWords = empty($displayOrdinaryWords) ? $ordinaryWords : $displayOrdinaryWords;
        $this->additionalMinusWords = $additionalMinusWords;
    }

    public function getKey(): string
    {
        $a = $this->ordinaryWords;
        sort($a);

        $b = $this->minusWords;
        sort($b);

        return join(" ", $a) . " " . join(" ", $b);
    }

    public function __toString(): string
    {
        $parts = array_merge($this->ordinaryWords, $this->minusWords);

        return join(";", $parts);
    }

    public function getOrdinaryWords(): array
    {
        return $this->ordinaryWords;
    }

    public function getMinusWords(): array
    {
        return $this->minusWords;
    }

    public function getAdditionalMinusWords(): array
    {
        return $this->additionalMinusWords;
    }

    public function addAdditionaMinusWord($word)
    {
        $this->additionalMinusWords[] = $word;
    }

    public function getAllMinusWords(): array
    {
        return array_merge($this->minusWords, $this->additionalMinusWords);
    }

    public function getDisplayOrdinaryWords(): array
    {
        return $this->displayOrdinaryWords;
    }

    public function inMinusWords($word)
    {
        $isExists = in_array($word, $this->minusWords);
        $isExists = $isExists || in_array($word, $this->additionalMinusWords);

        return $isExists;
    }
}

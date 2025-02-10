<?php

namespace App;

class PhraseProcessor
{
    private $processedPhrases = [];

    public function __construct(array $phrases)
    {
        $this->processedPhrases = self::process($phrases);
    }

    public static function process(array $phrases = []): array
    {
        $deduplicatedPhrases = self::deduplicate($phrases);
        $processedPhrases = self::applyMinusWords($deduplicatedPhrases);

        return $processedPhrases;
    }

    private static function deduplicate(array $phrases): array
    {
        $unique = [];
        foreach ($phrases as $phrase) {
            $unique[$phrase->getKey()] = $phrase;
        }

        return array_values($unique);
    }

    private static function applyMinusWords(array $phrases): array
    {
        $phrasesMap = [];
        foreach ($phrases as $phrase) {
            $phrasesMap[$phrase->getKey()] = $phrase;
        }

        $sortedPhrases = $phrases;
        usort($sortedPhrases, function ($a, $b) {
            return count($b->getOrdinaryWords()) - count($a->getOrdinaryWords());
        });

        foreach ($sortedPhrases as $i => $phrase) {
            $currentPhrase = $phrasesMap[$phrase->getKey()];
            $currentWords = $currentPhrase->getOrdinaryWords();

            for ($j = 0; $j < $i; $j++) {
                $otherPhrase = $phrasesMap[$sortedPhrases[$j]->getKey()];
                $otherWords = $otherPhrase->getOrdinaryWords();
                $diffWords = array_diff($currentWords, $otherWords);
                $isSubSet = count($diffWords) === 0 && count($otherWords) > count($currentWords);

                if ($isSubSet) {
                    $diff = array_diff($otherWords, $currentWords);
                    foreach ($diff as $word) {
                        $isNewMinusWord = !$currentPhrase->inMinusWords("-$word");
                        if ($isNewMinusWord) {
                            $currentPhrase->addAdditionaMinusWord("-$word");
                            $totalMinusWords["-$word"] = 1;
                        }
                    }
                }
            }
        }

        return array_values($phrasesMap);
    }

    public function getTableRows(): array
    {
        $allMinusWordsHash = [];
        $value = 0;
        foreach ($this->processedPhrases as $phrase) {
            foreach ($phrase->getAllMinusWords() as $key) {
                if (!isset($allMinusWordsHash[$key])) {
                    $allMinusWordsHash[$key] = $value++;
                }
            }
        }

        $rows = [];
        foreach ($this->processedPhrases as $index => $phrase) {
            $row = $this->getTableRow($phrase, $allMinusWordsHash);
            array_unshift($row, $index + 1);
            $rows[] = $row;
        }

        return $rows;
    }

    public function getTableRow($phrase, array $allMinusWordsHash): array
    {
        $displayMinusWords = array_fill(0, count($allMinusWordsHash), "");
        foreach ($phrase->getMinusWords() as $word) {
            $displayMinusWords[$allMinusWordsHash[$word]] = $word;
        }
        foreach ($phrase->getAdditionalMinusWords() as $word) {
            $displayMinusWords[$allMinusWordsHash[$word]] = $word;
        }

        return array_merge($phrase->getDisplayOrdinaryWords(), $displayMinusWords);
    }

    public function getTableHeader(int $columnNumber): array
    {
        $headers = range(1, $columnNumber - 1);
        array_unshift($headers, "#");

        return $headers;
    }

    public function getCSV(): string
    {
        $phrases = $this->processedPhrases;
        $rows = array_map(fn($phrase) => (string) $phrase, $phrases);

        return join("\n", $rows);
    }
}

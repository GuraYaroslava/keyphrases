<?php

namespace App;

class PhraseProcessor
{
    public static function process(array $phrases): array
    {
        $phrases = self::deduplicate($phrases);
        $result = self::applyMinusWords($phrases);

        return $result;
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
        $totalMinusWords = [];
        $phrasesMap = [];
        foreach ($phrases as $phrase) {
            $phrasesMap[$phrase->getKey()] = $phrase;
            foreach ($phrase->getMinusWords() as $word) {
                $totalMinusWords[$word] = 1;
            }
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

        $totalMinusWordsHash = [];
        if (count($totalMinusWords)) {
            $keys = array_keys($totalMinusWords);
            $values = range(0, count($keys) - 1);
            $totalMinusWordsHash = array_combine($keys, $values);
        }

        return [array_values($phrasesMap), $totalMinusWordsHash];
    }
}

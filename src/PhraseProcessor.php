<?php

namespace App;

class PhraseProcessor
{
    public static function process(array $phrases): array
    {
        $phrases = self::deduplicate($phrases);
        $phrases = self::applyMinusWords($phrases);

        return $phrases;
    }

    /**
     * Избавиться от дублей
     * @param array $phrases
     * @return array
     */
    private static function deduplicate(array $phrases): array
    {
        $unique = [];
        foreach ($phrases as $phrase) {
            $key = $phrase->getKey();
            if (!isset($unique[$key])) {
                $unique[$key] = $phrase;
            }
        }

        return array_values($unique);
    }

    /**
     * "Разминусовать" фразы так, чтобы они непересекались по ключевым словам
     * @param array $phrases
     * @return array
     */
    private static function applyMinusWords(array $phrases): array
    {
        // фразы с большим кол-вом обычных слов будут вначале массива
        usort($phrases, function ($a, $b) {
            return count($b->getOrdinaryWords()) - count($a->getOrdinaryWords());
        });

        foreach ($phrases as $i => $currentPhrase) {
            $currentWords = $currentPhrase->getOrdinaryWords();

            for ($j = 0; $j < $i; $j++) {
                $otherPhrase = $phrases[$j];
                $otherWords = $otherPhrase->getOrdinaryWords();
                $diffWords = array_diff($currentWords, $otherWords);
                $isSubSet = count($diffWords) === 0 && count($otherWords) > count($currentWords);

                if ($isSubSet) {
                    $diff = array_diff($otherWords, $currentWords);
                    foreach ($diff as $word) {
                        if (!in_array("-$word", $currentPhrase->getMinusWords())) {
                            $currentPhrase->addMinusWord("-$word");
                        }
                    }
                }
            }
        }

        return $phrases;
    }
}

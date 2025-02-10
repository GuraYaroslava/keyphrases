<?php

namespace App;

class PhraseGenerator
{
    public static function generate(array $groups): array
    {
        $elementsByGroup = array_map(function ($group) {
            $elementStrings = explode(',', trim($group));
            $elements = array_map([Element::class, 'fromString'], $elementStrings);

            $maxOrdinaryWordsNumber = 0;
            foreach ($elements as $element) {
                $words = $element->getOrdinaryWords();
                $maxWordsNumber = max($maxOrdinaryWordsNumber, count($words));
            }

            foreach ($elements as $element) {
                $element->setMaxDisplayOrdinaryWordsNumber($maxWordsNumber);
            }

            return $elements;
        }, $groups);

        $phrases = [];
        self::combineElements($elementsByGroup, 0, [], $phrases);

        return $phrases;
    }

    private static function combineElements(array $elementsByGroup, int $index, array $phraseElements, array &$phrases)
    {
        $canGeneratePhrase = $index === count($elementsByGroup);
        if ($canGeneratePhrase) {
            $displayOrdinaryWords = [];
            $ordinaryWords = [];
            $minusWords = [];

            foreach ($phraseElements as $element) {
                $ordinaryWords = array_merge($ordinaryWords, $element->getOrdinaryWords());
                $minusWords = array_merge($minusWords, $element->getMinusWords());

                $displayOrdinaryWords = array_merge($displayOrdinaryWords, $element->getOrdinaryWords());
                $emptyColumnNumber = $element->getMaxDisplayOrdinaryWordsNumber() - count($element->getOrdinaryWords());
                for ($i = 0; $i < $emptyColumnNumber; $i++) {
                    $displayOrdinaryWords[] = "";
                }
            }

            $phrases[] = new Phrase($ordinaryWords, $minusWords, $displayOrdinaryWords);

            return;
        }

        foreach ($elementsByGroup[$index] as $element) {
            $elements = $phraseElements;
            $elements[] = $element;
            self::combineElements($elementsByGroup, $index + 1, $elements, $phrases);
        }
    }
}

<?php

namespace App;

class PhraseGenerator
{
    /**
     * Сгенерировать все сочетания из элементов групп
     * @param array $groups
     * @return array
     */
    public static function generate(array $groups): array
    {
        $elementsByGroup = array_map(function ($group) {
            $elementStrings = explode(',', trim($group));
            $elements = array_map([Element::class, 'fromString'], $elementStrings);

            return $elements;
        }, $groups);

        $phrases = [];
        self::combineElements($elementsByGroup, 0, [], [], $phrases);

        return $phrases;
    }

    /**
     * Обойти все элементы групп, формируя все возможные их сочетания в фразы
     * @param array $elementsByGroup         Массив элементов по группам
     * @param int   $index                   Порядковый номер группы
     * @param array $currentOrdinaryWords    Массив обычных слов, присутствующих в фразе
     * @param array $currentMinusWords       Массив минус-слов, присутствующих в фразе
     * @param array $result                  Массив фраз
     * @return void
     */
    private static function combineElements(array $elementsByGroup, int $index, array $currentOrdinaryWords, array $currentMinusWords, array &$result)
    {
        $canGeneratePhrase = $index === count($elementsByGroup);
        if ($canGeneratePhrase) {
            $result[] = new Phrase($currentOrdinaryWords, $currentMinusWords);

            return;
        }

        foreach ($elementsByGroup[$index] as $element) {
            $newOrdinaryWords = array_merge($currentOrdinaryWords, $element->getOrdinaryWords());
            $newMinusWords = array_merge($currentMinusWords, $element->getMinusWords());

            self::combineElements($elementsByGroup, $index + 1, $newOrdinaryWords, $newMinusWords, $result);
        }
    }
}

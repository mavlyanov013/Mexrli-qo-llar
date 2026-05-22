<?php

namespace Database\Seeders\Concerns;

trait BuildsLocalizedRows
{
    protected function localizedRow(string $uz, ?string $ru = null): array
    {
        $uz = trim($uz);
        $ru = $ru !== null ? trim($ru) : null;

        return [
            'uz' => $uz,
            'oz' => $this->transliterateToCyrillic($uz),
            'ru' => $ru !== '' ? $ru : null,
        ];
    }

    protected function mergeLocalizedFields(array $base, array $fieldMap): array
    {
        foreach ($fieldMap as $field => $values) {
            $base["{$field}_uz"] = $values['uz'];
            $base["{$field}_oz"] = $values['oz'];
            $base["{$field}_ru"] = $values['ru'];
            $base[$field] = $values['uz'];
        }

        return $base;
    }

    private function transliterateToCyrillic(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $map = [
            'Sh' => 'Ш', 'sh' => 'ш', 'Ch' => 'Ч', 'ch' => 'ч',
            "O'" => 'Ў', "o'" => 'ў', 'G\'' => 'Ғ', 'g\'' => 'ғ',
            'Yo' => 'Ё', 'yo' => 'ё', 'Yu' => 'Ю', 'yu' => 'ю',
            'Ya' => 'Я', 'ya' => 'я', 'Ts' => 'Ц', 'ts' => 'ц',
        ];

        $result = $text;
        foreach ($map as $from => $to) {
            $result = str_replace($from, $to, $result);
        }

        $chars = [
            'A' => 'А', 'B' => 'Б', 'D' => 'Д', 'E' => 'Е', 'F' => 'Ф', 'G' => 'Г',
            'H' => 'Ҳ', 'I' => 'И', 'J' => 'Ж', 'K' => 'К', 'L' => 'Л', 'M' => 'М',
            'N' => 'Н', 'O' => 'О', 'P' => 'П', 'Q' => 'Қ', 'R' => 'Р', 'S' => 'С',
            'T' => 'Т', 'U' => 'У', 'V' => 'В', 'X' => 'Х', 'Y' => 'Й', 'Z' => 'З',
            'a' => 'а', 'b' => 'б', 'd' => 'д', 'e' => 'е', 'f' => 'ф', 'g' => 'г',
            'h' => 'ҳ', 'i' => 'и', 'j' => 'ж', 'k' => 'к', 'l' => 'л', 'm' => 'м',
            'n' => 'н', 'o' => 'о', 'p' => 'п', 'q' => 'қ', 'r' => 'р', 's' => 'с',
            't' => 'т', 'u' => 'у', 'v' => 'в', 'x' => 'х', 'y' => 'й', 'z' => 'з',
        ];

        return strtr($result, $chars);
    }
}

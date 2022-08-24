<?php

namespace Php\Project\Lvl2\Formatter;

use function Php\Project\Lvl2\Comparator\{getKey, getValue, isAdded, isDeleted};

function getFormatted($value, string $format): string
{
    if ($format === 'stylish') {
        return getStylished($value);
    }

    return 'Unknown format!';
}

function getStylished($value, int $depth = 0)
{
    if (is_string($value)) {
        return $value;
    } elseif (is_array($value)) {
        return getFormattedArray($value, $depth);
    }

    return json_encode($value);
}

function getFormattedArray(array $values, int $depth): string
{
    $replacer = ' ';
    $result = array_map(fn (
        $description
    ) => getFormattedRow($replacer, $description, $depth + 1), $values);

    $bracketIndent = str_repeat($replacer, $depth * 4);
    $result = ['{', ...$result, "{$bracketIndent}}"];

    return implode("\n", $result);
}

function getFormattedRow(string $replacer, $element, int $depth): string
{
    if (isAdded($element)) {
        $mark = '+';
    } elseif (isDeleted($element)) {
        $mark = '-';
    } else {
        $mark = ' ';
    }

    $mark .= ' ';

    $key = getKey($element);
    $value = getStylished(getValue($element), $depth);
    $indent = str_repeat($replacer, $depth * 4 - strlen($mark));

    return rtrim("{$indent}{$mark}{$key}: {$value}");
}

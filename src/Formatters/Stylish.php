<?php

namespace Php\Project\Lvl2\Formatters\Stylish;

use function Php\Project\Lvl2\Comparator\{
    getKey,
    getValue,
    isAdded,
    isComplex,
    isDeleted,
    isUpdated
};

const REPLACER = ' ';

function getFormatted($value, int $depth = 0)
{
    if (is_object($value)) {
        return getFormattedObject($value, $depth);
    } elseif (is_array($value)) {
        return getFormattedArray($value, $depth);
    }

    return toString($value);
}

function getFormattedArray(array $values, int $depth): string
{
    $result = array_map(function ($description) use ($depth) {
        if (isUpdated($description)) {
            $result = [];
            foreach (getValue($description) as $description) {
                $result[] = getFormattedRow(
                    getMark($description),
                    getKey($description),
                    getFormatted(getValue($description), $depth + 1),
                    $depth + 1
                );
            }

            return implode("\n", $result);
        }

        return getFormattedRow(
            getMark($description),
            getKey($description),
            getFormatted(getValue($description), $depth + 1),
            $depth + 1
        );
    }, $values);

    $bracketIndent = str_repeat(REPLACER, $depth * 4);
    $result = ['{', ...$result, "{$bracketIndent}}"];

    return implode("\n", $result);
}

function getFormattedObject(object $object, int $depth): string
{
    $result = [];

    foreach ($object as $key => $value) {
        $result[] = getFormattedRow(
            getMark($object),
            $key,
            isComplex($value) ? getFormattedObject($value, $depth + 1) : $value,
            $depth + 1
        );
    }

    $bracketIndent = str_repeat(REPLACER, $depth * 4);
    $result = ['{', ...$result, "{$bracketIndent}}"];

    return implode("\n", $result);
}

function getFormattedRow(string $mark, string $key, $value, int $depth): string
{
    $mark .= ' ';
    $indent = str_repeat(REPLACER, $depth * 4 - strlen($mark));

    return rtrim("{$indent}{$mark}{$key}: {$value}");
}

function getMark($value): string
{
    if (isComplex($value)) {
        $mark = ' ';
    } elseif (isAdded($value)) {
        $mark = '+';
    } elseif (isDeleted($value)) {
        $mark = '-';
    } else {
        $mark = ' ';
    }

    return $mark;
}

function toString($value)
{
    return str_replace('"', '', json_encode($value));
}

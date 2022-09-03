<?php

namespace Php\Project\Lvl2\Formatters\Stylish;

use Exception;

use function Php\Project\Lvl2\Comparator\{
    getKey,
    getKeys,
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
            $result = array_map(
                fn ($description) => getFormattedRow(
                    getMark($description),
                    getKey($description),
                    getFormatted(getValue($description), $depth + 1),
                    $depth + 1
                ),
                getValue($description)
            );

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
    $result = array_map(
        fn ($property) => getFormattedRow(
            getMark($object),
            $property,
            isComplex($object->$property) ? getFormattedObject($object->$property, $depth + 1) : $object->$property,
            $depth + 1
        ),
        getKeys($object)
    );

    $bracketIndent = str_repeat(REPLACER, $depth * 4);
    $result = ['{', ...$result, "{$bracketIndent}}"];

    return implode("\n", $result);
}

function getFormattedRow(string $mark, string $key, $value, int $depth): string
{
    $mark .= ' ';
    $indent = str_repeat(REPLACER, $depth * 4 - strlen($mark));

    return "{$indent}{$mark}{$key}: {$value}";
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

function toString($value): string
{
    $encodedValue = json_encode($value);

    if ($encodedValue === false) {
        throw new Exception("Could not encode value {$value}");
    }

    return str_replace('"', '', $encodedValue);
}

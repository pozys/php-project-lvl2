<?php

namespace Php\Project\Lvl2\Formatters\Stylish;

use Exception;

const REPLACER = ' ';

function getFormatted(mixed $value, int $depth = 0): string
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
    $formattedRows = array_map(function ($key) use ($values, $depth) {
        $description = $values[$key];
        if (isUpdated($description)) {
            $changedValues = getChildren($description);

            $result = array_map(fn (
                $description
            ) => getFormattedRow(
                getMark($description),
                $key,
                getFormatted(getValue($description), $depth + 1),
                $depth + 1
            ), $changedValues);

            return implode("\n", $result);
        }

        if (hasChildren($description)) {
            $children = getChildren($description);

            return getFormattedRow(
                getMark($description),
                $key,
                getFormattedArray($children, $depth + 1),
                $depth + 1
            );
        }

        return getFormattedRow(
            getMark($description),
            $key,
            getFormatted(getValue($description), $depth + 1),
            $depth + 1
        );
    }, array_keys($values));

    return formattedRowsToString($formattedRows, $depth);
}

function getFormattedObject(object $object, int $depth): string
{
    $formattedRows = array_map(
        fn ($property) => getFormattedRow(
            getMark($object),
            $property,
            isComplex($object->$property) ? getFormattedObject($object->$property, $depth + 1) : $object->$property,
            $depth + 1
        ),
        array_keys(get_object_vars($object))
    );

    return formattedRowsToString($formattedRows, $depth);
}

function formattedRowsToString(array $rows, int $depth)
{
    $bracketIndent = str_repeat(REPLACER, $depth * 4);
    $result = ['{', ...$rows, "{$bracketIndent}}"];

    return implode("\n", $result);
}

function getFormattedRow(string $mark, string $key, mixed $value, int $depth): string
{
    $markWithIndent = $mark . ' ';
    $indent = str_repeat(REPLACER, $depth * 4 - strlen($markWithIndent));

    return "{$indent}{$markWithIndent}{$key}: {$value}";
}

function getMark(mixed $value): string
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

function toString(mixed $value): string
{
    $encodedValue = json_encode($value);

    if ($encodedValue === false) {
        throw new Exception("Could not encode value {$value}");
    }

    return str_replace('"', '', $encodedValue);
}

function getValue(array $elem)
{
    return $elem['value'];
}

function getType(array $elem): string
{
    return $elem['type'];
}

function isAdded(array $description): bool
{
    return getType($description) === 'added';
}

function isDeleted(array $description): bool
{
    return getType($description) === 'deleted';
}

function isUpdated(array $description): bool
{
    return getType($description) === 'updated';
}

function isUnchanged(array $description): bool
{
    return getType($description) === 'unchanged';
}

function hasChildren(array $description): bool
{
    return getType($description) === 'hasChildren';
}

function getChildren(array $description): array
{
    return $description['children'];
}

function isComplex(mixed $value): bool
{
    return is_object($value);
}

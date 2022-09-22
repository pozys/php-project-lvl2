<?php

namespace Php\Project\Lvl2\Formatters\Plain;

use Exception;

use function Functional\flatten;

function getFormatted(array $data): string
{
    $formattedRows = getFormattedRows($data);

    return implode("\n", $formattedRows);
}

function getFormattedRows(array $values, array $pathToProperty = []): array
{
    $rows = array_map(
        function (string $key) use ($values, $pathToProperty) {
            $description = $values[$key];

            if (isUnchanged($description)) {
                return [];
            }

            if (hasChildren($description)) {
                return [...getFormattedRows(getChildren($description), [...$pathToProperty, $key])];
            }

            return [getFormattedRow($description, [...$pathToProperty, $key])];
        },
        array_keys($values)
    );

    return flatten($rows);
}

function getFormattedRow(array $description, array $pathToProperty): string
{
    if (isAdded($description)) {
        $formattedValue = getFormattedValue(getValue($description));
        $actionDescription =  "was added with value: {$formattedValue}";
    } elseif (isDeleted($description)) {
        $formattedValue = getFormattedValue(getValue($description));
        $actionDescription =  "was removed";
    } elseif (isUpdated($description)) {
        [$deleted, $added] = getChildren($description);
        $deletedValue = getFormattedValue(getValue($deleted));
        $addedValue = getFormattedValue(getValue($added));
        $actionDescription =  "was updated. From {$deletedValue} to {$addedValue}";
    } else {
        $encodedDescription = var_export($description, true);
        throw new Exception("Uknown type of description: \n {$encodedDescription}");
    }

    $fullNameProperty = implode('.', $pathToProperty);

    return "Property '{$fullNameProperty}' {$actionDescription}";
}

function getFormattedValue(mixed $value): string
{
    if (isComplex($value)) {
        return '[complex value]';
    }

    $formattedValue = toString($value);

    return is_string($value) ? "'{$formattedValue}'" : $formattedValue;
}

function toString(mixed $value)
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

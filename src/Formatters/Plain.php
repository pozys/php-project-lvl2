<?php

namespace Php\Project\Lvl2\Formatters\Plain;

use function Php\Project\Lvl2\Comparator\{
    getKey,
    getValue,
    hasChildren,
    isAdded,
    isComplex,
    isDeleted,
    isUnchanged,
    isUpdated
};

function getFormatted($value): string
{
    $formattedRows = getFormattedRows($value);

    return implode("\n", $formattedRows);
}

function getFormattedRows(array $values, array $pathToProperty = []): array
{
    $rows = array_reduce(
        $values,
        function (array $carry, $description) use ($pathToProperty) {
            $pathToProperty[] = getKey($description);
            if (isUnchanged($description)) {
                return $carry;
            }

            if (hasChildren($description)) {
                return [...$carry, ...getFormattedRows(getValue($description), $pathToProperty)];
            }

            $carry[] = getFormattedRow($description, $pathToProperty);

            return $carry;
        },
        []
    );

    return $rows;
}

function getFormattedRow(array $description, array $pathToProperty): string
{
    $value = getValue($description);

    if (isAdded($description)) {
        $formattedValue = getFormattedValue($value);
        $actionDescription =  "was added with value: {$formattedValue}";
    } elseif (isDeleted($description)) {
        $formattedValue = getFormattedValue($value);
        $actionDescription =  "was removed";
    } elseif (isUpdated($description)) {
        [$deleted, $added] = $value;
        $deletedValue = getFormattedValue(getValue($deleted));
        $addedValue = getFormattedValue(getValue($added));
        $actionDescription =  "was updated. From {$deletedValue} to {$addedValue}";
    }

    $pathToProperty = implode('.', $pathToProperty);

    return "Property '{$pathToProperty}' {$actionDescription}";
}

function getFormattedValue($value): string
{
    if (isComplex($value)) {
        return '[complex value]';
    }

    $formattedValue = toString($value);

    return is_string($value) ? "'{$formattedValue}'" : $formattedValue;
}

function toString($value)
{
    return str_replace('"', '', json_encode($value));
}

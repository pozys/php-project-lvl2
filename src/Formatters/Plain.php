<?php

namespace Php\Project\Lvl2\Formatters\Plain;

use Exception;

use function Functional\flatten;
use function Php\Project\Lvl2\Comparator\{
    getChildren,
    getValue,
    hasChildren,
    isAdded,
    isComplex,
    isDeleted,
    isUnchanged,
    isUpdated
};

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
            $pathToProperty[] = $key;
            if (isUnchanged($description)) {
                return [];
            }

            if (hasChildren($description)) {
                return [...getFormattedRows(getChildren($description), $pathToProperty)];
            }

            return [getFormattedRow($description, $pathToProperty)];
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

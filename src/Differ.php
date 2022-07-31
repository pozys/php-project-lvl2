<?php

namespace Php\Project\Lvl2\Differ;

use Exception;

const ADDED_MARK = 'added';
const DELETED_MARK = 'deleted';
const UNCHANGED_MARK = 'unchanged';
const UPDATED_MARK = 'updated';

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    if (!(file_exists($pathToFile1) && file_exists($pathToFile2))) {
        return 'Один или несколько файлов не найдены.';
    }

    $data1 = json_decode(file_get_contents($pathToFile1), true);
    $data2 = json_decode(file_get_contents($pathToFile2), true);

    $deleted = getDeleted($data1, $data2);
    $added = getAdded($data1, $data2);

    $checkingKeys = array_keys(array_diff_key($data1, $deleted, $added));

    $updated = getUpdatedData($checkingKeys, $data1, $data2);

    $unchanged = getUnchanged($data1, $deleted, $added, $updated);

    $result = array_merge($deleted, $added, $updated, $unchanged);

    $result = getSortedData($result);

    return getFormattedResult($result);
}

function getDeleted(array $data1, array $data2): array
{
    $deletedValues = array_diff_key($data1, $data2);

    return array_map(fn ($value) => ['type' => DELETED_MARK, 'value' => $value], $deletedValues);
}

function getAdded(array $data1, array $data2): array
{
    $addedValues = array_diff_key($data2, $data1);

    return array_map(fn ($value) => ['type' => ADDED_MARK, 'value' => $value], $addedValues);
}

function getUnchanged(
    array $originalValues,
    array $deletedValues,
    array $addedValues,
    array $updatedValues
): array {
    $unchangedValues = array_diff_key($originalValues, $deletedValues, $addedValues, $updatedValues);

    return array_map(fn ($value) => ['type' => UNCHANGED_MARK, 'value' => $value], $unchangedValues);
}

function isEqual($value1, $value2): bool
{
    return $value1 === $value2;
}

function getUpdatedData(array $keys, array $data1, array $data2): array
{
    $changes = [];

    foreach ($keys as $key) {
        $value1 = $data1[$key];
        $value2 = $data2[$key];

        if (isEqual($value1, $value2)) {
            continue;
        }

        $changes[$key] = [
            'type' => UPDATED_MARK,
            'oldValue' => $value1,
            'newValue' => $value2,
        ];
    }

    return $changes;
}

function getSortedData(array $data): array
{
    ksort($data);
    return $data;
}

function getFormattedResult(array $data): string
{
    $indentSymbol = '  ';
    $indentCount = 1;
    $indent = str_repeat($indentSymbol, $indentCount);
    $result = [];
    $result[] = '{';

    foreach ($data as $key => $description) {
        switch ($description['type']) {
            case ADDED_MARK:
                $result[] = getFormattedRow($indent, '+', $key, $description['value']);
                break;
            case DELETED_MARK:
                $result[] = getFormattedRow($indent, '-', $key, $description['value']);
                break;
            case UNCHANGED_MARK:
                $result[] = getFormattedRow($indent, ' ', $key, $description['value']);
                break;
            case UPDATED_MARK:
                $result[] = getFormattedRow($indent, '-', $key, $description['oldValue']);
                $result[] = getFormattedRow($indent, '+', $key, $description['newValue']);
                break;
            default:
                throw new Exception('Unknown object type');
                break;
        }
    }

    $result[] = '}';

    $result = implode("\n", $result);

    return $result;
}

function getFormattedRow(string $indent, string $mark, string $key, $value): string
{
    $value = getFormattedValue($value);
    return "{$indent}{$mark} {$key}: {$value}";
}

function getFormattedValue($value)
{
    return is_string($value) ? $value : json_encode($value);
}

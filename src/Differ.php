<?php

namespace Php\Project\Lvl2\Differ;

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    if (!(file_exists($pathToFile1) && file_exists($pathToFile2))) {
        return 'Один или несколько файлов не найдены.';
    }

    $data1 = json_decode(file_get_contents($pathToFile1), true);
    $data2 = json_decode(file_get_contents($pathToFile2), true);

    $deletedValues = array_diff_key($data1, $data2);
    $addedValues = array_diff_key($data2, $data1);
    $checkingKeys = array_keys(array_diff_key($data1, $deletedValues, $addedValues));

    [$firstFileChanges, $secondFileChanges] = getChangedData($checkingKeys, $data1, $data2);

    $firstFileChanges = array_merge($firstFileChanges, $deletedValues);
    $secondFileChanges = array_merge($secondFileChanges, $addedValues);
    $unchangedValues = array_diff_key($data1, $firstFileChanges, $secondFileChanges);

    $firstFileChanges = getMarkedData($firstFileChanges, '-');
    $secondFileChanges = getMarkedData($secondFileChanges, '+');
    $unchangedValues = getMarkedData($unchangedValues, ' ');

    $result = array_merge($firstFileChanges, $secondFileChanges, $unchangedValues);
    $result = getSortedData($result);

    return getFormattedResult($result);
}

function isEqual($value1, $value2): bool
{
    return $value1 === $value2;
}

function getChangedData(array $keys, array $data1, array $data2): array
{
    $firstFileChanges = [];
    $secondFileChanges = [];

    foreach ($keys as $key) {
        $value1 = $data1[$key];
        $value2 = $data2[$key];

        if (isEqual($value1, $value2)) {
            continue;
        }

        $firstFileChanges[$key] = $value1;
        $secondFileChanges[$key] = $value2;
    }

    return [$firstFileChanges, $secondFileChanges];
}

function getMarkedData(array $data, string $mark): array
{
    $keys = array_keys($data);
    $values = array_values($data);

    $markedKeys = getMarkedKeys($keys, $mark);

    return array_combine($markedKeys, $values);
}

function getMarkedKeys(array $keys, string $mark): array
{
    return array_map(fn ($key) => "${mark} ${key}", $keys);
}

function getSortedData(array $data): array
{
    uksort($data, function ($key1, $key2) {
        $key1WithoutMark = mb_substr($key1, - (mb_strlen($key1) - 1));
        $key2WithoutMark = mb_substr($key2, - (mb_strlen($key2) - 1));

        if ($key1WithoutMark === $key2WithoutMark) {
            return - ($key1 <=> $key2);
        }

        return $key1WithoutMark <=> $key2WithoutMark;
    });

    return $data;
}

function getFormattedResult(array $data): string
{
    $result = json_encode($data, JSON_PRETTY_PRINT);
    $result = str_replace('"', '', $result);

    return $result;
}

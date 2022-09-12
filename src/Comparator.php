<?php

namespace Php\Project\Lvl2\Comparator;

use function Functional\sort;

const ADDED_MARK = 'added';
const DELETED_MARK = 'deleted';
const HAS_CHILDREN_MARK = 'hasChildren';
const UNCHANGED_MARK = 'unchanged';
const UPDATED_MARK = 'updated';

function getComparedData(object $data1, object $data2): array
{
    $result = getDifferences($data1, $data2);

    return getSortedData($result);
}

function getDifferences(object $data1, object $data2): array
{
    $deleted = getDeleted($data1, $data2);
    $added = getAdded($data1, $data2);

    $checkingValues = getCheckingValues($data1, $added, $deleted);
    $checkingKeys = array_keys($checkingValues);
    $comparedValues = array_map(
        function ($key) use ($data1, $data2) {
            return getCheckedData($data1, $data2, $key);
        },
        array_keys($checkingValues)
    );

    $compared = array_combine($checkingKeys, $comparedValues);

    return array_merge($deleted, $added, $compared);
}

function getDeleted(object $data1, object $data2): array
{
    $deletedValues = array_diff_key(get_object_vars($data1), get_object_vars($data2));

    return array_map(fn ($value) => markAsDeleted($value), $deletedValues);
}

function getAdded(object $data1, object $data2): array
{
    $addedValues = array_diff_key(get_object_vars($data2), get_object_vars($data1));

    return array_map(fn ($value) => markAsAdded($value), $addedValues);
}

function getCheckingValues(object $original, array $added, array $deleted): array
{
    return array_diff_key(
        get_object_vars($original),
        $added,
        $deleted
    );
}

function markAsAdded(mixed $value): array
{
    return getDescription(ADDED_MARK, $value);
}

function markAsDeleted(mixed $value): array
{
    return getDescription(DELETED_MARK, $value);
}

function markAsUpdated(mixed $oldValue, mixed $newValue): array
{
    $value = [getDescription(DELETED_MARK, $oldValue), getDescription(ADDED_MARK, $newValue)];

    return getDescription(UPDATED_MARK, null, $value);
}

function markAsUnchanged(mixed $value): array
{
    return getDescription(UNCHANGED_MARK, $value);
}

function getDescription(string $type, mixed $value = null, array $children = []): array
{
    $properties = ['type', $children === [] ? 'value' : 'children'];

    return compact($properties);
}

function isComplex(mixed $value): bool
{
    return is_object($value);
}

function isEqual(mixed $value1, mixed $value2): bool
{
    return $value1 === $value2;
}

function getCheckedData(object $data1, object $data2, string $key): array
{
    $value1 = $data1->{$key};
    $value2 = $data2->{$key};

    if (isComplex($value1) && isComplex($value2)) {
        return getDescription(HAS_CHILDREN_MARK, null, (getComparedData($value1, $value2)));
    }

    if (isEqual($value1, $value2)) {
        return markAsUnchanged($value1);
    }

    return markAsUpdated($value1, $value2);
}

function getSortedData(array $data): array
{
    $sortedKeys = sort(array_keys($data), function ($elem1, $elem2) use ($data) {
        if (strcmp($elem1, $elem2) === 0) {
            return getType($data[$elem1]) === DELETED_MARK ? -1 : 1;
        }

        return strcmp($elem1, $elem2);
    });

    $sortedValues = array_map(fn ($key) => $data[$key], $sortedKeys);

    return array_combine($sortedKeys, $sortedValues);
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
    return getType($description) === ADDED_MARK;
}

function isDeleted(array $description): bool
{
    return getType($description) === DELETED_MARK;
}

function isUpdated(array $description): bool
{
    return getType($description) === UPDATED_MARK;
}

function isUnchanged(array $description): bool
{
    return getType($description) === UNCHANGED_MARK;
}

function hasChildren(array $description): bool
{
    return getType($description) === HAS_CHILDREN_MARK;
}

function getChildren(array $description): array
{
    return $description['children'];
}

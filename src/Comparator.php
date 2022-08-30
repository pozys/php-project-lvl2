<?php

namespace Php\Project\Lvl2\Comparator;

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

    $compared = array_reduce(
        array_keys($checkingValues),
        function ($carry, $key) use ($data1, $data2) {
            $carry[] = getCheckedData($data1, $data2, $key);

            return $carry;
        },
        []
    );

    return array_merge($deleted, $added, $compared);
}

function getDeleted(object $data1, object $data2): array
{
    $deletedValues = array_diff_key(get_object_vars($data1), get_object_vars($data2));

    return array_map(fn ($key) => markAsDeleted($key, $deletedValues[$key]), array_keys($deletedValues));
}

function getKeys($values)
{
    if (is_object($values)) {
        $values = get_object_vars($values);
    }

    if (is_array($values)) {
        return array_keys($values);
    }

    return $values;
}

function getAdded(object $data1, object $data2): array
{
    $addedValues = array_diff_key(get_object_vars($data2), get_object_vars($data1));

    return array_map(fn ($key) => markAsAdded($key, $addedValues[$key]), array_keys($addedValues));
}

function getCheckingValues(object $original, array $added, array $deleted): array
{
    return array_diff_key(
        get_object_vars($original),
        array_column($added, 'value', 'key'),
        array_column($deleted, 'value', 'key')
    );
}

function markAsAdded(string $key, $value): array
{
    return getDescription($value, $key, ADDED_MARK);
}

function markAsDeleted(string $key, $value): array
{
    return getDescription($value, $key, DELETED_MARK);
}

function markAsUpdated(string $key, $oldValue, $newValue): array
{
    $value = [getDescription($oldValue, $key, DELETED_MARK), getDescription($newValue, $key, ADDED_MARK)];

    return getDescription($value, $key, UPDATED_MARK);
}

function markAsUnchanged($value, string $key): array
{
    return getDescription($value, $key, UNCHANGED_MARK);
}

function getDescription($value, string $key, string $type): array
{
    return compact('type', 'key', 'value');
}

function isComplex($value): bool
{
    return is_object($value);
}

function isEqual($value1, $value2): bool
{
    return $value1 === $value2;
}

function getCheckedData(object $data1, object $data2, string $key): array
{
    $value1 = $data1->{$key};
    $value2 = $data2->{$key};

    if (isComplex($value1) && isComplex($value2)) {
        return getDescription(getComparedData($value1, $value2), $key, HAS_CHILDREN_MARK);
    }

    if (isEqual($value1, $value2)) {
        return markAsUnchanged($value1, $key);
    }

    return markAsUpdated($key, $value1, $value2);
}

function getSortedData(array $data): array
{
    uasort(
        $data,
        function ($elem1, $elem2) {
            if (getKey($elem1) === getKey($elem2)) {
                return getType($elem1) === DELETED_MARK ? -1 : 1;
            }

            return getKey($elem1) <=> getKey($elem2);
        }
    );

    return $data;
}

function getKey(array $elem): string
{
    return $elem['key'];
}

function getValue(array $elem)
{
    return $elem['value'];
}

function setValue(array $elem, $value)
{
    return $elem['value'] = $value;
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

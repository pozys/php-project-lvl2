<?php

namespace Php\Project\Lvl2\Formatters\Json;

use function Php\Project\Lvl2\Comparator\{
    getKey,
    getValue,
    isAdded,
    isComplex,
    isDeleted,
    isUpdated
};

const REPLACER = ' ';

function getFormatted($value)
{
    return json_encode($value);
}

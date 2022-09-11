<?php

namespace Differ\Differ;

use function Php\Project\Lvl2\Comparator\getComparedData;
use function Php\Project\Lvl2\Formatters\Formatters\getFormattedData;
use function Php\Project\Lvl2\Parser\getParsedData;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $data1 = getParsedData($pathToFile1);
    $data2 = getParsedData($pathToFile2);

    $result = getComparedData($data1, $data2);

    return getFormattedData($result, $format);
}

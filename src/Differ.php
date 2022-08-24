<?php

namespace Php\Project\Lvl2\Differ;

use function Php\Project\Lvl2\Comparator\getComparedData;
use function Php\Project\Lvl2\Formatter\getFormatted;
use function Php\Project\Lvl2\Parser\getParsedData;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $data1 = getParsedData($pathToFile1);
    $data2 = getParsedData($pathToFile2);

    if (is_null($data1) || is_null($data2)) {
        return 'Произошла ошибка при обработке файлов';
    }

    $result = getComparedData($data1, $data2);

    return getFormatted($result, $format);
}

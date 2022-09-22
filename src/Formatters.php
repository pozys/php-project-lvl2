<?php

namespace Php\Project\Lvl2\Formatters;

use Exception;

use function Php\Project\Lvl2\Formatters\Json\getFormatted as getJsonFormatted;
use function Php\Project\Lvl2\Formatters\Plain\getFormatted as getPlainFormatted;
use function Php\Project\Lvl2\Formatters\Stylish\getFormatted as getStylishFormatted;

function getFormattedData(mixed $value, string $format): string
{
    $formatter = getFormatter($format);

    return $formatter($value);
}

function getFormatter(string $format): callable
{
    switch ($format) {
        case 'stylish':
            return fn ($value) => getStylishFormatted($value);
        case 'plain':
            return fn ($value) => getPlainFormatted($value);
        case 'json':
            return fn ($value) => getJsonFormatted($value);
        default:
            throw new Exception("Uknown output format '{$format}'");
    }
}

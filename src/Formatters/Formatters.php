<?php

namespace Php\Project\Lvl2\Formatters\Formatters;

use function Php\Project\Lvl2\Formatters\Json\getFormatted as getJsonFormatted;
use function Php\Project\Lvl2\Formatters\Plain\getFormatted as GetPlainFormatted;
use function Php\Project\Lvl2\Formatters\Stylish\getFormatted as GetStylishFormatted;

const ALLOWED_FORMATS = ['stylish', 'plain', 'json'];

function getFormattedData(mixed $value, string $format): string
{
    if (!in_array($format, ALLOWED_FORMATS)) {
        return 'Unknown format!';
    }

    $formatter = getFormatter($format);

    return $formatter($value);
}

function getFormatter(string $format): callable
{
    if ($format === 'stylish') {
        return fn ($value) => GetStylishFormatted($value);
    } elseif ($format === 'plain') {
        return fn ($value) => GetPlainFormatted($value);
    } elseif ($format === 'json') {
        return fn ($value) => getJsonFormatted($value);
    }
}

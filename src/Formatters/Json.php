<?php

namespace Php\Project\Lvl2\Formatters\Json;

function getFormatted(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT);
}

<?php

namespace Php\Project\Lvl2\Parser;

use Symfony\Component\Yaml\Yaml;

function getParsedData(string $filePath): ?array
{
    if (!file_exists($filePath)) {
        return null;
    }

    $data = file_get_contents($filePath);

    if ($data === false) {
        return null;
    }

    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    $parser = getParser($extension);

    return $parser($data);
}

function getParser(string $extension): callable
{
    return function ($data) use ($extension) {
        if (in_array($extension, ['yaml', 'yml'])) {
            return Yaml::parse($data);
        } else {
            return json_decode($data, true);
        }
    };
}

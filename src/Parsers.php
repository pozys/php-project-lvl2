<?php

namespace Php\Project\Lvl2\Parser;

use Exception;
use Symfony\Component\Yaml\Yaml;

function getParsedData(string $filePath): object
{
    if (!file_exists($filePath)) {
        throw new Exception("File '{$filePath}' doesn't exist");
    }

    $data = file_get_contents($filePath);

    if ($data === false) {
        throw new Exception("Could not read file '{$filePath}'");
    }

    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    $parser = getParser($extension);

    return $parser($data);
}

function getParser(string $extension): callable
{
    return function ($data) use ($extension) {
        switch ($extension) {
            case 'yml';
            case 'yaml';
                return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
            case 'json':
                return json_decode($data);
            default:
                throw new Exception("Uknown file extension '{$extension}'");
        }
    };
}

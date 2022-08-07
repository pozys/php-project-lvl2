<?php

namespace Php\Project\Lvl2\tests;

use PHPUnit\Framework\TestCase;

use function Php\Project\Lvl2\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testPlainJson()
    {
        $expected = file_get_contents('tests/fixtures/expected');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.json', 'tests/fixtures/file2.json'));
    }

    public function testPlainYaml()
    {
        $expected = file_get_contents('tests/fixtures/expected');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.yml', 'tests/fixtures/file2.yml'));
    }
}

<?php

namespace Php\Project\Lvl2\tests;

use PHPUnit\Framework\TestCase;

use function Php\Project\Lvl2\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testStylishFormattedJson()
    {
        $expected = file_get_contents('tests/fixtures/expected-stylish');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.json', 'tests/fixtures/file2.json'));
    }

    public function testStylishFormattedYaml()
    {
        $expected = file_get_contents('tests/fixtures/expected-stylish');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.yml', 'tests/fixtures/file2.yml'));
    }

    public function testPlainFormattedJson()
    {
        $expected = file_get_contents('tests/fixtures/expected-plain');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.json', 'tests/fixtures/file2.json', 'plain'));
    }

    public function testPlainFormattedYaml()
    {
        $expected = file_get_contents('tests/fixtures/expected-plain');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.yml', 'tests/fixtures/file2.yml', 'plain'));
    }
}

<?php

namespace Php\Project\Lvl2\tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Php\Project\Lvl2\Formatters\getFormattedData;

class DifferTest extends TestCase
{
    public function getFixtureFullPath($fixtureName)
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function getExpected(string $fileName)
    {
        return file_get_contents($this->getFixtureFullPath($fileName));
    }

    public function runAsserting(string $expected, string $format = 'stylish')
    {
        print_r("{$format}\n");
        $this->assertEquals(
            $expected,
            getFormattedData(
                genDiff($this->getFixtureFullPath('file1.json'), $this->getFixtureFullPath('file2.json')),
                $format
            )
        );

        $this->assertEquals(
            $expected,
            getFormattedData(
                genDiff($this->getFixtureFullPath('file1.yml'), $this->getFixtureFullPath('file2.yml')),
                $format
            )
        );
    }

    public function testByDefaultFormatted()
    {
        $expected = $this->getExpected('expected-stylish');

        $this->runAsserting($expected);
    }

    public function testStylishFormatted()
    {
        $expected = $this->getExpected('expected-stylish');

        $this->runAsserting($expected, 'stylish');
    }

    public function testPlainFormatted()
    {
        $expected = $this->getExpected('expected-plain');

        $this->runAsserting($expected, 'plain');
    }

    public function testJsonFormatted()
    {
        $expected = $this->getExpected('expected-json');

        $this->runAsserting($expected, 'json');
    }
}

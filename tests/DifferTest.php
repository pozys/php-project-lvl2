<?php

namespace Php\Project\Lvl2\tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function getFixtureFullPath($fixtureName): string
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function getExpected(string $fileName)
    {
        return file_get_contents($this->getFixtureFullPath($fileName));
    }

    public function fileProvider(): array
    {
        return [
            'JSON-files' => [
                $this->getFixtureFullPath('file1.json'),
                $this->getFixtureFullPath('file2.json'),
            ],
            'YAML-files' => [
                $this->getFixtureFullPath('file1.yml'),
                $this->getFixtureFullPath('file2.yml'),
            ],
        ];
    }

    /**
     * @dataProvider fileProvider
     */
    public function testByDefaultFormatted($file1, $file2): void
    {
        $expected = $this->getExpected('expected-stylish');

        $this->assertEquals($expected, genDiff($file1, $file2));
    }

    /**
     * @dataProvider fileProvider
     */
    public function testStylishFormatted($file1, $file2): void
    {
        $expected = $this->getExpected('expected-stylish');
        $this->assertEquals($expected, genDiff($file1, $file2, 'stylish'));
    }

    /**
     * @dataProvider fileProvider
     */
    public function testPlainFormatted($file1, $file2): void
    {
        $expected = $this->getExpected('expected-plain');
        $this->assertEquals($expected, genDiff($file1, $file2, 'plain'));
    }

    /**
     * @dataProvider fileProvider
     */
    public function testJsonFormatted($file1, $file2): void
    {
        $expected = $this->getExpected('expected-json');
        $this->assertEquals($expected, genDiff($file1, $file2, 'json'));
    }
}

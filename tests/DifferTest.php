<?php

namespace Php\Project\Lvl2\tests;

use PHPUnit\Framework\TestCase;

use function Php\Project\Lvl2\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testDiffer()
    {
        $expected = file_get_contents('tests/fixtures/expected');

        $this->assertEquals($expected, genDiff('tests/fixtures/file1.json', 'tests/fixtures/file2.json'));
    }
}

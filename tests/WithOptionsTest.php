<?php
/**
 * User: ingvar.aasen
 * Date: 2026-05-11
 */

use Iaasen\WithOptions;

class WithOptionsTest extends \PHPUnit\Framework\TestCase
{

    public function testExtractWithReturnsDefaultWhenNull()
    {
        $default = ['foo', 'bar' => []];

        $result = WithOptions::extractWith(null, $default);

        $this->assertSame($default, $result);
    }

    public function testExtractWithArrayReturnsSameArray()
    {
        $input = ['foo', 'bar' => []];

        $result = WithOptions::extractWith($input);

        $this->assertSame($input, $result);
    }

    public function testExtractWithArrayReturnsFullOnAllKeyword()
    {
        $input = ['all'];
        $full = ['a' => [], 'b' => []];

        $result = WithOptions::extractWith($input, [], $full);

        $this->assertSame($full, $result);
    }

    public function testExtractWithEmptyStringReturnsEmptyArray()
    {
        $result = WithOptions::extractWith('');

        $this->assertSame([], $result);
    }

    public function testExtractWithJson()
    {
        $json = '{"0":"foo","bar":{"foobar":[]}}';
        $result = WithOptions::extractWith($json);

        $this->assertSame(['foo', 'bar' => ['foobar' => []]], $result);
    }

    public function testExtractWithCommaSeparatedList()
    {
        $input = 'a,b,c';

        $result = WithOptions::extractWith($input);

        $this->assertSame([
            'a' => [],
            'b' => [],
            'c' => []
        ], $result);
    }

    public function testExtractWithCommaSeparatedAll()
    {
        $input = 'a,all';
        $full = ['x' => [], 'y' => []];

        $result = WithOptions::extractWith($input, [], $full);

        $this->assertSame($full, $result);
    }

    public function testGraphQlParsing()
    {
        $input = '{foo bar { foobar } }';

        $result = WithOptions::extractWith($input);

        $this->assertEquals([
            'foo',
            'bar' => [
                'foobar'
            ]
        ], $result);
    }

    public function testFilterWithArray()
    {
        $received = [
            'a' => [],
            'b' => ['c' => [], 'd' => []]
        ];

        $reference = [
            'a' => [],
            'b' => ['c' => []],
            'e' => []
        ];

        $result = WithOptions::filterWithArray($received, $reference);

        $this->assertSame([
            'a' => [],
            'b' => ['c' => []]
        ], $result);
    }

    public function testSeparateWithFromWithout()
    {
        $input = [
            'user' => ['name', '!password'],
            'posts' => [
                'title',
                '!draft'
            ]
        ];

        [$with, $without] = WithOptions::separateWithFromWithout($input);

        $this->assertSame([
            'user' => ['name'],
            'posts' => ['title']
        ], $with);

        $this->assertSame([
            'user' => ['password'],
            'posts' => ['draft']
        ], $without);
    }

    public function testIsValidJson()
    {
        $this->assertTrue(WithOptions::isValidJson('{"a":1}'));
        $this->assertFalse(WithOptions::isValidJson('{invalid json}'));
    }

    public function testIsValidGraphQlSelectionSet()
    {
        $this->assertTrue(WithOptions::isValidGraphQlSelectionSet('{a b { c }}'));
        $this->assertFalse(WithOptions::isValidGraphQlSelectionSet('a b c'));
    }

}

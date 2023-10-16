<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Stringable;

use Hyperf\Stringable\Str;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 * @coversNothing
 */
class StrTest extends TestCase
{
    public function testCharAt()
    {
        $this->assertEquals('р', Str::charAt('Привет, мир!', 1));
        $this->assertEquals('ち', Str::charAt('「こんにちは世界」', 4));
        $this->assertEquals('w', Str::charAt('Привет, world!', 8));
        $this->assertEquals('界', Str::charAt('「こんにちは世界」', -2));
        $this->assertEquals(null, Str::charAt('「こんにちは世界」', -200));
        $this->assertEquals(null, Str::charAt('Привет, мир!', 100));
    }

    public function testSlug()
    {
        $res = Str::slug('hyperf_', '_');

        $this->assertSame('hyperf', $res);

        $arr = [
            '0' => 0,
            '1' => 1,
            'a' => 'a',
        ];

        $this->assertSame([0, 1, 'a' => 'a'], $arr);
        foreach ($arr as $i => $v) {
            $this->assertIsInt($i);
            break;
        }

        $this->assertSame('hello-world', Str::slug('hello world'));
        $this->assertSame('hello-world', Str::slug('hello-world'));
        $this->assertSame('hello-world', Str::slug('hello_world'));
        $this->assertSame('hello_world', Str::slug('hello_world', '_'));
        $this->assertSame('user-at-host', Str::slug('user@host'));
        $this->assertSame('سلام-دنیا', Str::slug('سلام دنیا', '-', null));
        $this->assertSame('sometext', Str::slug('some text', ''));
        $this->assertSame('', Str::slug('', ''));
        $this->assertSame('', Str::slug(''));
        $this->assertSame('bsm-allah', Str::slug('بسم الله', '-', 'en', ['allh' => 'allah']));
        $this->assertSame('500-dollar-bill', Str::slug('500$ bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500--$----bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500-$-bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500$--bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500-$--bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('أحمد-في-المدرسة', Str::slug('أحمد@المدرسة', '-', null, ['@' => 'في']));
    }

    public function testMask()
    {
        $res = Str::mask('hyperf');

        $this->assertSame('******', $res);

        $res = Str::mask('hyperf', 3);

        $this->assertSame('hyp***', $res);

        $res = Str::mask('hyperf', 3, 1);

        $this->assertSame('hyp*rf', $res);

        $res = Str::mask('hyperf', 0, 3);

        $this->assertSame('***erf', $res);

        $res = Str::mask('hyperf', 0, 0, '-');

        $this->assertSame('------', $res);

        $res = Str::mask('hyperf', 6, 2);

        $this->assertSame('hyperf', $res);

        $res = Str::mask('hyperf', 7);

        $this->assertSame('hyperf', $res);

        $res = Str::mask('hyperf', 3, 10);

        $this->assertSame('hyp**********', $res);

        $res = Str::mask('hyperf', -3);
        $this->assertSame('***erf', $res);

        $res = Str::mask('hyperf', -3, 1);
        $this->assertSame('hy*erf', $res);

        $res = Str::mask('hyperf', -3, 3);
        $this->assertSame('***erf', $res);

        $res = Str::mask('hyperf', -3, 5);
        $this->assertSame('*****erf', $res);

        $res = Str::mask('你好啊');

        $this->assertSame('***', $res);

        $res = Str::mask('你好世界', 3);

        $this->assertSame('你好世*', $res);

        $res = Str::mask('你好世界', 2, 1);

        $this->assertSame('你好*界', $res);

        $res = Str::mask('你好世界', 0, 3);

        $this->assertSame('***界', $res);

        $res = Str::mask('你好世界', 1, 1);

        $this->assertSame('你*世界', $res);

        $res = Str::mask('你好世界', 0, 0, '-');

        $this->assertSame('----', $res);

        $res = Str::mask('你好世界', 6, 2);

        $this->assertSame('你好世界', $res);

        $res = Str::mask('你好世界', 7);

        $this->assertSame('你好世界', $res);

        $res = Str::mask('你好世界', 3, 10);

        $this->assertSame('你好世**********', $res);

        $res = Str::mask('你好世界', -1);
        $this->assertSame('***界', $res);

        $res = Str::mask('你好世界', -1, 1);
        $this->assertSame('你好*界', $res);

        $res = Str::mask('你好世界', -3, 3);
        $this->assertSame('***好世界', $res);

        $this->expectException(InvalidArgumentException::class);
        Str::mask('hyperf', -1, -1);
    }

    public function testStartsWith()
    {
        $this->assertFalse(Str::startsWith('hyperf.wiki', 'http://'));
        $this->assertFalse(Str::startsWith('hyperf.wiki', ['http://', 'https://']));
        $this->assertTrue(Str::startsWith('http://www.hyperf.io', 'http://'));
        $this->assertTrue(Str::startsWith('https://www.hyperf.io', ['http://', 'https://']));
        $this->assertFalse(Str::startsWith('Hyperf', ['']));
        $this->assertFalse(Str::startsWith('Hyperf', [null]));
        $this->assertFalse(Str::startsWith('Hyperf', null));
    }

    public function testStripTags()
    {
        $this->assertSame('beforeafter', Str::stripTags('before<br>after'));
        $this->assertSame('before<br>after', Str::stripTags('before<br>after', '<br>'));
        $this->assertSame('before<br>after', Str::stripTags('<strong>before</strong><br>after', '<br>'));
        $this->assertSame('<strong>before</strong><br>after', Str::stripTags('<strong>before</strong><br>after', '<br><strong>'));

        if (PHP_VERSION_ID >= 70400) {
            $this->assertSame('<strong>before</strong><br>after', Str::stripTags('<strong>before</strong><br>after', ['<br>', '<strong>']));
        }

        if (PHP_VERSION_ID >= 80000) {
            $this->assertSame('beforeafter', Str::stripTags('before<br>after', null));
        }
    }

    public function testPadBoth()
    {
        $this->assertSame('__Alien___', Str::padBoth('Alien', 10, '_'));
        $this->assertSame('  Alien   ', Str::padBoth('Alien', 10));
        $this->assertSame('  ❤MultiByte☆   ', Str::padBoth('❤MultiByte☆', 16));
    }

    public function testPadLeft()
    {
        $this->assertSame('-=-=-Alien', Str::padLeft('Alien', 10, '-='));
        $this->assertSame('     Alien', Str::padLeft('Alien', 10));
        $this->assertSame('     ❤MultiByte☆', Str::padLeft('❤MultiByte☆', 16));
    }

    public function testPadRight()
    {
        $this->assertSame('Alien-----', Str::padRight('Alien', 10, '-'));
        $this->assertSame('Alien     ', Str::padRight('Alien', 10));
        $this->assertSame('❤MultiByte☆     ', Str::padRight('❤MultiByte☆', 16));
    }

    public function testLength()
    {
        $this->assertEquals(11, Str::length('foo bar baz'));
        $this->assertEquals(11, Str::length('foo bar baz', 'UTF-8'));
    }

    public function testUlid()
    {
        $this->assertTrue(Str::isUlid((string) Str::ulid()));
    }

    public function testUuid()
    {
        $this->assertInstanceOf(UuidInterface::class, $uuid = Str::uuid());
        $this->assertTrue(Str::isUuid((string) $uuid));

        $this->assertInstanceOf(UuidInterface::class, $uuid = Str::orderedUuid());
        $this->assertTrue(Str::isUuid((string) $uuid));
    }

    public function testIsMatch()
    {
        $this->assertTrue(Str::isMatch('/.*,.*!/', 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch('/^.*$(.*)/', 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch('/laravel/i', 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch('/^(.*(.*(.*)))/', 'Hello, Laravel!'));

        $this->assertFalse(Str::isMatch('/H.o/', 'Hello, Laravel!'));
        $this->assertFalse(Str::isMatch('/^laravel!/i', 'Hello, Laravel!'));
        $this->assertFalse(Str::isMatch('/laravel!(.*)/', 'Hello, Laravel!'));
        $this->assertFalse(Str::isMatch('/^[a-zA-Z,!]+$/', 'Hello, Laravel!'));

        $this->assertTrue(Str::isMatch(['/.*,.*!/', '/H.o/'], 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch(['/^laravel!/i', '/^.*$(.*)/'], 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch(['/laravel/i', '/laravel!(.*)/'], 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch(['/^[a-zA-Z,!]+$/', '/^(.*(.*(.*)))/'], 'Hello, Laravel!'));
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('Hyperf', ['h'], true));
        $this->assertTrue(Str::contains('Hyperf', ['H']));
        $this->assertFalse(Str::contains('Hyperf', ['']));
        $this->assertFalse(Str::contains('Hyperf', [null]));
        $this->assertFalse(Str::contains('Hyperf', null));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith('Hyperf', ['f']));
        $this->assertFalse(Str::endsWith('Hyperf', ['']));
        $this->assertFalse(Str::endsWith('Hyperf', [null]));
        $this->assertFalse(Str::endsWith('Hyperf', null));
    }

    public function testContainsAll()
    {
        $this->assertTrue(Str::containsAll('Hyperf', ['h'], true));
        $this->assertFalse(Str::containsAll('Hyperf', ['h']));
    }

    public function testStrBetweenFirst()
    {
        $data = [
            ['abc', ['abc', '', 'c']],
            ['abc', ['abc', 'a', '']],
            ['abc', ['abc', '', '']],
            ['b', ['abc', 'a', 'c']],
            ['b', ['dddabc', 'a', 'c']],
            ['b', ['abcddd', 'a', 'c']],
            ['b', ['dddabcddd', 'a', 'c']],
            ['nn', ['hannah', 'ha', 'ah']],
            ['a', ['[a]ab[b]', '[', ']']],
            ['foo', ['foofoobar', 'foo', 'bar']],
            ['', ['foobarbar', 'foo', 'bar']],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::betweenFirst(...$item[1]));
        }
    }

    public function testExcerpt()
    {
        $this->assertSame('...is a beautiful morn...', Str::excerpt('This is a beautiful morning', 'beautiful', ['radius' => 5]));
        $this->assertSame('This is a...', Str::excerpt('This is a beautiful morning', 'this', ['radius' => 5]));
        $this->assertSame('...iful morning', Str::excerpt('This is a beautiful morning', 'morning', ['radius' => 5]));
        $this->assertNull(Str::excerpt('This is a beautiful morning', 'day'));
        $this->assertSame('...is a beautiful! mor...', Str::excerpt('This is a beautiful! morning', 'Beautiful', ['radius' => 5]));
        $this->assertSame('...is a beautiful? mor...', Str::excerpt('This is a beautiful? morning', 'beautiful', ['radius' => 5]));
        $this->assertSame('', Str::excerpt('', '', ['radius' => 0]));
        $this->assertSame('a', Str::excerpt('a', 'a', ['radius' => 0]));
        // $this->assertSame('...b...', Str::excerpt('abc', 'B', ['radius' => 0]));
        $this->assertSame('abc', Str::excerpt('abc', 'b', ['radius' => 1]));
        $this->assertSame('abc...', Str::excerpt('abcd', 'b', ['radius' => 1]));
        $this->assertSame('...abc', Str::excerpt('zabc', 'b', ['radius' => 1]));
        $this->assertSame('...abc...', Str::excerpt('zabcd', 'b', ['radius' => 1]));
        $this->assertSame('zabcd', Str::excerpt('zabcd', 'b', ['radius' => 2]));
        $this->assertSame('zabcd', Str::excerpt('  zabcd  ', 'b', ['radius' => 4]));
        $this->assertSame('...abc...', Str::excerpt('z  abc  d', 'b', ['radius' => 1]));
        $this->assertSame('[...]is a beautiful morn[...]', Str::excerpt('This is a beautiful morning', 'beautiful', ['omission' => '[...]', 'radius' => 5]));
        $this->assertSame(
            'This is the ultimate supercalifragilisticexpialidoceous very looooooooooooooooooong looooooooooooong beautiful morning with amazing sunshine and awesome tempera[...]',
            Str::excerpt(
                'This is the ultimate supercalifragilisticexpialidoceous very looooooooooooooooooong looooooooooooong beautiful morning with amazing sunshine and awesome temperatures. So what are you gonna do about it?',
                'very',
                ['omission' => '[...]'],
            )
        );

        $this->assertSame('...y...', Str::excerpt('taylor', 'y', ['radius' => 0]));
        $this->assertSame('...ayl...', Str::excerpt('taylor', 'Y', ['radius' => 1]));
        $this->assertSame('<div> The article description </div>', Str::excerpt('<div> The article description </div>', 'article'));
        $this->assertSame('...The article desc...', Str::excerpt('<div> The article description </div>', 'article', ['radius' => 5]));
        $this->assertSame('The article description', Str::excerpt(strip_tags('<div> The article description </div>'), 'article'));
        $this->assertSame('', Str::excerpt(null));
        $this->assertSame('', Str::excerpt(''));
        $this->assertSame('', Str::excerpt(null));
        $this->assertSame('T...', Str::excerpt('The article description', null, ['radius' => 1]));
        $this->assertSame('The arti...', Str::excerpt('The article description', '', ['radius' => 8]));
        $this->assertSame('', Str::excerpt(' '));
        $this->assertSame('The arti...', Str::excerpt('The article description', ' ', ['radius' => 4]));
        $this->assertSame('...cle description', Str::excerpt('The article description', 'description', ['radius' => 4]));
        $this->assertSame('T...', Str::excerpt('The article description', 'T', ['radius' => 0]));
        $this->assertSame('What i?', Str::excerpt('What is the article?', 'What', ['radius' => 2, 'omission' => '?']));

        $this->assertSame('...ö - 二 sān 大åè...', Str::excerpt('åèö - 二 sān 大åèö', '二 sān', ['radius' => 4]));
        $this->assertSame('åèö - 二...', Str::excerpt('åèö - 二 sān 大åèö', 'åèö', ['radius' => 4]));
        $this->assertSame('åèö - 二 sān 大åèö', Str::excerpt('åèö - 二 sān 大åèö', 'åèö - 二 sān 大åèö', ['radius' => 4]));
        $this->assertSame('åèö - 二 sān 大åèö', Str::excerpt('åèö - 二 sān 大åèö', 'åèö - 二 sān 大åèö', ['radius' => 4]));
        $this->assertSame('...༼...', Str::excerpt('㏗༼㏗', '༼', ['radius' => 0]));
        $this->assertSame('...༼...', Str::excerpt('㏗༼㏗', '༼', ['radius' => 0]));
        $this->assertSame('...ocê e...', Str::excerpt('Como você está', 'ê', ['radius' => 2]));
        $this->assertSame('...ocê e...', Str::excerpt('Como você está', 'Ê', ['radius' => 2]));
        $this->assertSame('João...', Str::excerpt('João Antônio ', 'jo', ['radius' => 2]));
        $this->assertSame('João Antô...', Str::excerpt('João Antônio', 'JOÃO', ['radius' => 5]));
    }

    public function testIsJson()
    {
        $data = [
            [true, '1'],
            [true, '[1,2,3]'],
            [true, '[1,   2,   3]'],
            [true, '{"first": "John", "last": "Doe"}'],
            [true, '[{"first": "John", "last": "Doe"}, {"first": "Jane", "last": "Doe"}]'],
            [false, '1,'],
            [false, '[1,2,3'],
            [false, '[1,   2   3]'],
            [false, '{first: "John"}'],
            [false, '[{first: "John"}, {first: "Jane"}]'],
            [false, ''],
            [false, null],
            [false, []],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::isJson($item[1]));
        }
    }

    public function testLcfirst()
    {
        $data = [
            ['laravel', 'Laravel'],
            ['laravel framework', 'Laravel framework'],
            ['мама', 'Мама'],
            ['мама мыла раму', 'Мама мыла раму'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::lcfirst($item[1]));
        }
    }

    public function testUcsplit()
    {
        $data = [
            [['Laravel_p_h_p_framework'], 'Laravel_p_h_p_framework'],
            [['Laravel_', 'P_h_p_framework'], 'Laravel_P_h_p_framework'],
            [['laravel', 'P', 'H', 'P', 'Framework'], 'laravelPHPFramework'],
            [['Laravel-ph', 'P-framework'], 'Laravel-phP-framework'],
            [['Żółta', 'Łódka'], 'ŻółtaŁódka'],
            [['sind', 'Öde', 'Und', 'So'], 'sindÖdeUndSo'],
            [['Öffentliche', 'Überraschungen'], 'ÖffentlicheÜberraschungen'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::ucsplit($item[1]));
        }
    }

    public function testIsUuidWithValidUuid()
    {
        $this->assertTrue(Str::isUuid(Str::uuid()->__toString()));
    }

    public function testIsUuidWithInvalidUuid()
    {
        $this->assertFalse(Str::isUuid('foo'));
    }

    public function testWordCount()
    {
        $data = [
            [2, 'Hello, world!'],
            [10, 'Hi, this is my first contribution to the Laravel framework.'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::wordCount($item[1]));
        }
    }

    public function testPassword()
    {
        $data = [
            [32, []],
            [10, [10]],
        ];
        foreach ($data as $item) {
            $this->assertIsString(Str::password(...$item[1]));
            $this->assertSame($item[0], strlen(Str::password(...$item[1])));
        }
    }

    public function testReplaceStart()
    {
        $data = [
            ['foobar foobar', ['bar', 'qux', 'foobar foobar']],
            ['foo/bar? foo/bar?', ['bar?', 'qux?', 'foo/bar? foo/bar?']],
            ['quxbar foobar', ['foo', 'qux', 'foobar foobar']],
            ['qux? foo/bar?', ['foo/bar?', 'qux?', 'foo/bar? foo/bar?']],
            ['bar foobar', ['foo', '', 'foobar foobar']],
            ['1', [0, '1', '0']],
            ['xxxnköping Malmö', ['Jö', 'xxx', 'Jönköping Malmö']],
            ['Jönköping Malmö', ['', 'yyy', 'Jönköping Malmö']],
        ];

        foreach ($data as $item) {
            $this->assertSame($item[0], Str::replaceStart(...$item[1]));
        }
    }

    public function testReplaceEnd()
    {
        $data = [
            ['foobar fooqux', ['bar', 'qux', 'foobar foobar']],
            ['foo/bar? foo/qux?', ['bar?', 'qux?', 'foo/bar? foo/bar?']],
            ['foobar foo', ['bar', '', 'foobar foobar']],
            ['foobar foobar', ['xxx', 'yyy', 'foobar foobar']],
            ['foobar foobar', ['', 'yyy', 'foobar foobar']],
            ['fooxxx foobar', ['xxx', 'yyy', 'fooxxx foobar']],
            ['Malmö Jönköping', ['ö', 'xxx', 'Malmö Jönköping']],
            ['Malmö Jönkyyy', ['öping', 'yyy', 'Malmö Jönköping']],
        ];

        foreach ($data as $item) {
            $this->assertSame($item[0], Str::replaceEnd(...$item[1]));
        }
    }

    public function testReverse()
    {
        $data = [
            ['FooBar', 'raBooF'],
            ['Teniszütő', 'őtüzsineT'],
            ['❤MultiByte☆', '☆etyBitluM❤'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::reverse($item[1]));
        }
    }

    public function testSquish()
    {
        $data = [
            ['laravel php framework', ' laravel   php  framework '],
            ['laravel php framework', "laravel\t\tphp\n\nframework"],
            ['laravel php framework', '
            laravel
            php
            framework
        '],
            ['laravel php framework', 'laravelㅤㅤㅤphpㅤframework'],
            ['laravel php framework', 'laravelᅠᅠᅠᅠᅠᅠᅠᅠᅠᅠphpᅠᅠframework'],
            ['laravel php framework', '   laravel   php   framework   '],
            ['123', '   123    '],
            ['だ', 'だ'],
            ['ム', 'ム'],
            ['だ', '   だ    '],
            ['ム', '   ム    '],
            ['ム', '﻿   ム ﻿﻿   ﻿'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::squish($item[1]));
        }
    }

    public function testSubstrReplace()
    {
        $this->assertSame('12:00', Str::substrReplace('1200', ':', 2, 0));
        $this->assertSame('The Laravel Framework', Str::substrReplace('The Framework', 'Laravel ', 4, 0));
        $this->assertSame('Laravel – The PHP Framework for Web Artisans', Str::substrReplace('Laravel Framework', '– The PHP Framework for Web Artisans', 8));
    }

    public function testSwapKeywords()
    {
        $this->assertSame(
            'PHP 8 is fantastic',
            Str::swap([
                'PHP' => 'PHP 8',
                'awesome' => 'fantastic',
            ], 'PHP is awesome')
        );

        $this->assertSame(
            'foo bar baz',
            Str::swap([
                'ⓐⓑ' => 'baz',
            ], 'foo bar ⓐⓑ')
        );
    }

    public function testWrap()
    {
        $this->assertEquals('"value"', Str::wrap('value', '"'));
        $this->assertEquals('foo-bar-baz', Str::wrap('-bar-', 'foo', 'baz'));
    }

    public function testWordWrap()
    {
        $data = [
            ['Hello<br />World', ['Hello World', 3, '<br />']],
            ['Hel<br />lo<br />Wor<br />ld', ['Hello World', 3, '<br />', true]],
            ['❤Multi<br />Byte☆❤☆❤☆❤', ['❤Multi Byte☆❤☆❤☆❤', 3, '<br />']],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::wordWrap(...$item[1]));
        }
    }

    public function testConvertCase()
    {
        $data = [
            ['MARY HAD A LITTLE LAMB AND SHE LOVED IT SO', ['mary had a Little lamb and she loved it so', MB_CASE_UPPER, 'UTF-8']],
            ['Mary Had A Little Lamb And She Loved It So', ['mary had a Little lamb and she loved it so', MB_CASE_TITLE, 'UTF-8']],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::convertCase(...$item[1]));
        }
    }

    public function testReplaceLast()
    {
        $this->assertSame('Hello earth', Str::replaceLast('world', 'earth', 'Hello world'));
        $this->assertSame('Hello world', Str::replaceLast('', 'earth', 'Hello world'));
    }
}

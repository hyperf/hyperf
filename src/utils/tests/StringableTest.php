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
namespace HyperfTest\Utils;

use Hyperf\Utils\Stringable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StringableTest extends TestCase
{
    public function testCharAt()
    {
        $this->assertEquals('р', $this->stringable('Привет, мир!')->charAt(1));
        $this->assertEquals('ち', $this->stringable('「こんにちは世界」')->charAt(4));
        $this->assertEquals('w', $this->stringable('Привет, world!')->charAt(8));
        $this->assertEquals('界', $this->stringable('「こんにちは世界」')->charAt(-2));
        $this->assertEquals(null, $this->stringable('「こんにちは世界」')->charAt(-200));
        $this->assertEquals(null, $this->stringable('Привет, мир!')->charAt('Привет, мир!', 100));
    }

    public function testExactly()
    {
        $this->assertTrue($this->stringable('foo')->exactly($this->stringable('foo')));
        $this->assertTrue($this->stringable('foo')->exactly('foo'));

        $this->assertFalse($this->stringable('Foo')->exactly($this->stringable('foo')));
        $this->assertFalse($this->stringable('Foo')->exactly('foo'));
        $this->assertFalse($this->stringable('[]')->exactly([]));
        $this->assertFalse($this->stringable('0')->exactly(0));
    }

    public function testSlug()
    {
        $str = $this->stringable('hyperf_')->slug('_')->__toString();

        $this->assertSame('hyperf', $str);
    }

    public function testMask()
    {
        $str = $this->stringable('hyperf');
        $this->assertSame('******', $str->mask()->__toString());
        $this->assertSame('hyp***', $str->mask(3)->__toString());
        $this->assertSame('hyp*rf', $str->mask(3, 1)->__toString());
        $this->assertSame('***erf', $str->mask(0, 3)->__toString());
        $this->assertSame('------', $str->mask(0, 0, '-')->__toString());
        $this->assertSame('hyperf', $str->mask(6, 2)->__toString());
        $this->assertSame('hyperf', $str->mask(7)->__toString());
        $this->assertSame('hyp**********', $str->mask(3, 10)->__toString());
        $this->assertSame('***erf', $str->mask(-3)->__toString());
        $this->assertSame('hy*erf', $str->mask(-3, 1)->__toString());
        $this->assertSame('***erf', $str->mask(-3, 3)->__toString());
        $this->assertSame('*****erf', $str->mask(-3, 5)->__toString());

        $str = $this->stringable('你好啊');
        $this->assertSame('***', $str->mask()->__toString());

        $str = $this->stringable('你好世界');
        $this->assertSame('你好世*', $str->mask(3)->__toString());
        $this->assertSame('你好*界', $str->mask(2, 1)->__toString());
        $this->assertSame('***界', $str->mask(0, 3)->__toString());
        $this->assertSame('你*世界', $str->mask(1, 1)->__toString());
        $this->assertSame('----', $str->mask(0, 0, '-')->__toString());
        $this->assertSame('你好世界', $str->mask(6, 2)->__toString());
        $this->assertSame('你好世界', $str->mask(7)->__toString());
        $this->assertSame('你好世**********', $str->mask(3, 10)->__toString());
        $this->assertSame('***界', $str->mask(-1)->__toString());
        $this->assertSame('你好*界', $str->mask(-1, 1)->__toString());
        $this->assertSame('***好世界', $str->mask(-3, 3)->__toString());

        $this->expectException(InvalidArgumentException::class);
        $this->stringable('hyperf')->mask(-1, -1);
    }

    public function testStartsWith()
    {
        $this->assertFalse($this->stringable('hyperf.wiki')->startsWith('http://'));
        $this->assertFalse($this->stringable('hyperf.wiki')->startsWith(['http://', 'https://']));
        $this->assertTrue($this->stringable('http://www.hyperf.io')->startsWith('http://'));
        $this->assertTrue($this->stringable('https://www.hyperf.io')->startsWith(['http://', 'https://']));
    }

    public function testStripTags()
    {
        $this->assertSame('beforeafter', (string) $this->stringable('before<br>after')->stripTags());
        $this->assertSame('before<br>after', (string) $this->stringable('before<br>after')->stripTags('<br>'));
        $this->assertSame('before<br>after', (string) $this->stringable('<strong>before</strong><br>after')->stripTags('<br>'));
        $this->assertSame('<strong>before</strong><br>after', (string) $this->stringable('<strong>before</strong><br>after')->stripTags('<br><strong>'));

        if (PHP_VERSION_ID >= 70400) {
            $this->assertSame('<strong>before</strong><br>after', (string) $this->stringable('<strong>before</strong><br>after')->stripTags(['<br>', '<strong>']));
        }

        if (PHP_VERSION_ID >= 80000) {
            $this->assertSame('beforeafter', (string) $this->stringable('before<br>after')->stripTags(null));
        }
    }

    public function testWhenEmptyAndNot()
    {
        $empty = $this->stringable('');
        $this->assertTrue($empty->whenEmpty(function ($str, $value) {
            $this->assertTrue($value);
            return true;
        }));

        $this->assertSame($empty, $empty->whenEmpty(function ($str, $value) {
            $this->assertTrue($value);
            return null;
        }, function () {
            return true;
        }));

        $notEmpty = $this->stringable('123');
        $this->assertTrue($notEmpty->whenEmpty(function ($str, $value) {
            $this->assertFalse($value);
            return false;
        }, function () {
            return true;
        }));
    }

    public function testWhenAndUnless()
    {
        $str = $this->stringable('Hyperf is the best PHP framework');

        $this->assertSame('Hyperf is the best PHP framework!!!', $str->when(fn ($str) => $str->contains('best'), function ($str) {
            return $str->append('!!!');
        })->__toString());
        $this->assertSame('Hyperf is the best PHP framework!!!', $str->unless(fn ($str) => $str->contains('!!!'), function ($str) {
            return $str->append('!!!');
        })->__toString());
    }

    public function testArrayAccess()
    {
        $str = $this->stringable('my string');
        $this->assertSame('m', $str[0]);
        $this->assertSame('t', $str[4]);
        $this->assertTrue(isset($str[2]));
        $this->assertFalse(isset($str[10]));

        $str[0] = 'M';
        $this->assertSame('My string', (string) $str);
    }

    /**
     * @param string $string
     * @return Stringable
     */
    protected function stringable($string = '')
    {
        return new Stringable($string);
    }
}

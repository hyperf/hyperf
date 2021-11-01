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

use Hyperf\Utils\Exception\InvalidArgumentException;
use Hyperf\Utils\Str;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StringableTest extends TestCase
{
    public function testSlug()
    {
        $str = Str::of('hyperf_')->slug('_')->__toString();

        $this->assertSame('hyperf', $str);
    }

    public function testMask()
    {
        $str = Str::of('hyperf');

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

        $str = Str::of('你好啊');

        $this->assertSame('***', $str->mask()->__toString());

        $str = Str::of('你好世界');

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
        Str::of('hyperf')->mask(-1, -1);
    }

    public function testStartsWith()
    {
        $this->assertFalse(Str::of('hyperf.wiki')->startsWith('http://'));
        $this->assertFalse(Str::of('hyperf.wiki')->startsWith(['http://', 'https://']));
        $this->assertTrue(Str::of('http://www.hyperf.io')->startsWith('http://'));
        $this->assertTrue(Str::of('https://www.hyperf.io')->startsWith(['http://', 'https://']));
    }

    public function testStripTags()
    {
        $this->assertSame('beforeafter', (string) Str::of('before<br>after')->stripTags());
        $this->assertSame('before<br>after', (string) Str::of('before<br>after')->stripTags('<br>'));
        $this->assertSame('before<br>after', (string) Str::of('<strong>before</strong><br>after')->stripTags('<br>'));
        $this->assertSame('<strong>before</strong><br>after', (string) Str::of('<strong>before</strong><br>after')->stripTags('<br><strong>'));

        if (PHP_VERSION_ID >= 70400) {
            $this->assertSame('<strong>before</strong><br>after', (string) Str::of('<strong>before</strong><br>after')->stripTags(['<br>', '<strong>']));
        }

        if (PHP_VERSION_ID >= 80000) {
            $this->assertSame('beforeafter', (string) Str::of('before<br>after')->stripTags(null));
        }
    }
}

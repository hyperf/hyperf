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

namespace HyperfTest\Support;

use Hyperf\Support\Env;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Hyperf\Support\env;

/**
 * @internal
 * @coversNothing
 */
class EnvTest extends TestCase
{
    public function testNull()
    {
        $id = 'NULL_' . uniqid();
        putenv("{$id}=(null)");

        $this->assertNull(env($id));
    }

    public function testHit()
    {
        $_SERVER['foo'] = 'bar';
        $this->assertSame('bar', env('foo'));
        $this->assertSame('bar', Env::get('foo'));
    }

    public function testEnvTrue()
    {
        $_SERVER['foo'] = 'true';
        $this->assertTrue(env('foo'));

        $_SERVER['foo'] = '(true)';
        $this->assertTrue(env('foo'));
    }

    public function testEnvFalse()
    {
        $_SERVER['foo'] = 'false';
        $this->assertFalse(env('foo'));

        $_SERVER['foo'] = '(false)';
        $this->assertFalse(env('foo'));
    }

    public function testEnvEmpty()
    {
        $_SERVER['foo'] = '';
        $this->assertSame('', env('foo'));

        $_SERVER['foo'] = 'empty';
        $this->assertSame('', env('foo'));

        $_SERVER['foo'] = '(empty)';
        $this->assertSame('', env('foo'));
    }

    public function testEnvNull()
    {
        $_SERVER['foo'] = 'null';
        $this->assertNull(env('foo'));

        $_SERVER['foo'] = '(null)';
        $this->assertNull(env('foo'));
    }

    public function testEnvDefault()
    {
        $_SERVER['foo'] = 'bar';
        $this->assertSame('bar', env('foo', 'default'));

        $_SERVER['foo'] = '';
        $this->assertSame('', env('foo', 'default'));

        unset($_SERVER['foo']);
        $this->assertSame('default', env('foo', 'default'));

        $_SERVER['foo'] = null;
        $this->assertSame('default', env('foo', 'default'));
    }

    public function testEnvEscapedString()
    {
        $_SERVER['foo'] = '"null"';
        $this->assertSame('null', env('foo'));

        $_SERVER['foo'] = "'null'";
        $this->assertSame('null', env('foo'));

        $_SERVER['foo'] = 'x"null"x'; // this should not be unquoted
        $this->assertSame('x"null"x', env('foo'));
    }

    public function testGetFromSERVERFirst()
    {
        $_ENV['foo'] = 'From $_ENV';
        $_SERVER['foo'] = 'From $_SERVER';
        $this->assertSame('From $_SERVER', env('foo'));
    }

    public function testRequiredEnvVariableThrowsAnExceptionWhenNotFound(): void
    {
        $this->expectExceptionObject(new RuntimeException('[required-does-not-exist] has no value'));

        Env::getOrFail('required-does-not-exist');
    }

    public function testRequiredEnvReturnsValue(): void
    {
        $_SERVER['required-exists'] = 'some-value';
        $this->assertSame('some-value', Env::getOrFail('required-exists'));
    }
}

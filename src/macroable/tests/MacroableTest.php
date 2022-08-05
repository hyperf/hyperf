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
namespace HyperfTest\Macroable;

use Hyperf\Macroable\Macroable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MacroableTest extends TestCase
{
    private $macroable;

    protected function setUp(): void
    {
        $this->macroable = $this->createObjectForTrait();
    }

    public function testRegisterMacro()
    {
        $macroable = $this->macroable;
        $macroable::macro(self::class, fn() => 'Taylor');
        $this->assertSame('Taylor', $macroable::{self::class}());
    }

    public function testRegisterMacroAndCallWithoutStatic()
    {
        $macroable = $this->macroable;
        $macroable::macro(self::class, fn() => 'Taylor');
        $this->assertSame('Taylor', $macroable->{self::class}());
    }

    public function testWhenCallingMacroClosureIsBoundToObject()
    {
        TestMacroable::macro('tryInstance', fn() => $this->protectedVariable);
        TestMacroable::macro('tryStatic', fn() => static::getProtectedStatic());
        $instance = new TestMacroable();

        $result = $instance->tryInstance();
        $this->assertSame('instance', $result);

        $result = TestMacroable::tryStatic();
        $this->assertSame('static', $result);
    }

    public function testClassBasedMacros()
    {
        TestMacroable::mixin(new TestMixin());
        $instance = new TestMacroable();
        $this->assertSame('instance-Adam', $instance->methodOne('Adam'));
    }

    public function testClassBasedMacrosNoReplace()
    {
        TestMacroable::macro('methodThree', fn() => 'bar');
        TestMacroable::mixin(new TestMixin(), false);
        $instance = new TestMacroable();
        $this->assertSame('bar', $instance->methodThree());

        TestMacroable::mixin(new TestMixin());
        $this->assertSame('foo', $instance->methodThree());
    }

    private function createObjectForTrait()
    {
        return new EmptyMacroable();
    }
}

class EmptyMacroable
{
    use Macroable;
}

class TestMacroable
{
    use Macroable;

    protected $protectedVariable = 'instance';

    protected static function getProtectedStatic()
    {
        return 'static';
    }
}

class TestMixin
{
    public function methodOne()
    {
        return fn($value) => $this->methodTwo();
    }

    protected function methodTwo()
    {
        return fn($value) => $this->protectedVariable . '-' . $value;
    }

    protected function methodThree()
    {
        return fn() => 'foo';
    }
}

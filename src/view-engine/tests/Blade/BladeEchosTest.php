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

namespace HyperfTest\ViewEngine\Blade;

use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class BladeEchosTest extends AbstractBladeTestCase
{
    public function testEchosAreCompiled()
    {
        $this->assertSame('<?php echo $name; ?>', $this->compiler->compileString('{!!$name!!}'));
        $this->assertSame('<?php echo $name; ?>', $this->compiler->compileString('{!! $name !!}'));
        $this->assertSame('<?php echo $name; ?>', $this->compiler->compileString('{!!
            $name
        !!}'));

        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e($name); ?>', $this->compiler->compileString('{{{$name}}}'));
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e($name); ?>', $this->compiler->compileString('{{$name}}'));
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e($name); ?>', $this->compiler->compileString('{{ $name }}'));
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e($name); ?>', $this->compiler->compileString('{{
            $name
        }}'));
        $this->assertSame("<?php echo \\Hyperf\\ViewEngine\\T::e(\$name); ?>\n\n", $this->compiler->compileString("{{ \$name }}\n"));
        $this->assertSame("<?php echo \\Hyperf\\ViewEngine\\T::e(\$name); ?>\r\n\r\n", $this->compiler->compileString("{{ \$name }}\r\n"));
        $this->assertSame("<?php echo \\Hyperf\\ViewEngine\\T::e(\$name); ?>\n\n", $this->compiler->compileString("{{ \$name }}\n"));
        $this->assertSame("<?php echo \\Hyperf\\ViewEngine\\T::e(\$name); ?>\r\n\r\n", $this->compiler->compileString("{{ \$name }}\r\n"));

        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e("Hello world or foo"); ?>',
            $this->compiler->compileString('{{ "Hello world or foo" }}')
        );
        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e("Hello world or foo"); ?>',
            $this->compiler->compileString('{{"Hello world or foo"}}')
        );
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e($foo + $or + $baz); ?>', $this->compiler->compileString('{{$foo + $or + $baz}}'));
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e("Hello world or foo"); ?>', $this->compiler->compileString('{{
            "Hello world or foo"
        }}'));

        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e(\'Hello world or foo\'); ?>',
            $this->compiler->compileString('{{ \'Hello world or foo\' }}')
        );
        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e(\'Hello world or foo\'); ?>',
            $this->compiler->compileString('{{\'Hello world or foo\'}}')
        );
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::e(\'Hello world or foo\'); ?>', $this->compiler->compileString('{{
            \'Hello world or foo\'
        }}'));

        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e(myfunc(\'foo or bar\')); ?>',
            $this->compiler->compileString('{{ myfunc(\'foo or bar\') }}')
        );
        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e(myfunc("foo or bar")); ?>',
            $this->compiler->compileString('{{ myfunc("foo or bar") }}')
        );
        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e(myfunc("$name or \'foo\'")); ?>',
            $this->compiler->compileString('{{ myfunc("$name or \'foo\'") }}')
        );
    }

    public function testEscapedWithAtEchosAreCompiled()
    {
        $this->assertSame('{{$name}}', $this->compiler->compileString('@{{$name}}'));
        $this->assertSame('{{ $name }}', $this->compiler->compileString('@{{ $name }}'));
        $this->assertSame(
            '{{
            $name
        }}',
            $this->compiler->compileString('@{{
            $name
        }}')
        );
        $this->assertSame(
            '{{ $name }}
            ',
            $this->compiler->compileString('@{{ $name }}
            ')
        );
    }

    public function testBladeHandlerCanInterceptRegularEchos()
    {
        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e($exampleObject); ?>',
            $this->compiler->compileString('{{$exampleObject}}')
        );
    }

    public function testBladeHandlerCanInterceptRawEchos()
    {
        $this->assertSame(
            '<?php echo $exampleObject; ?>',
            $this->compiler->compileString('{!!$exampleObject!!}')
        );
    }

    public function testBladeHandlerCanInterceptEscapedEchos()
    {
        $this->assertSame(
            '<?php echo \Hyperf\ViewEngine\T::e($exampleObject); ?>',
            $this->compiler->compileString('{{{$exampleObject}}}')
        );
    }

    public function testExpressionsOnTheSameLine()
    {
        $this->assertSame('<?php echo \Hyperf\ViewEngine\T::translator()->get(foo(bar(baz(qux(breeze()))))); ?> space () <?php echo \Hyperf\ViewEngine\T::translator()->get(foo(bar)); ?>', $this->compiler->compileString('@lang(foo(bar(baz(qux(breeze()))))) space () @lang(foo(bar))'));
    }

    public function testExpressionWithinHTML()
    {
        $this->assertSame('<html <?php echo \Hyperf\ViewEngine\T::e($foo); ?>>', $this->compiler->compileString('<html {{ $foo }}>'));
        $this->assertSame('<html<?php echo \Hyperf\ViewEngine\T::e($foo); ?>>', $this->compiler->compileString('<html{{ $foo }}>'));
        $this->assertSame('<html <?php echo \Hyperf\ViewEngine\T::e($foo); ?> <?php echo \Hyperf\ViewEngine\T::translator()->get(\'foo\'); ?>>', $this->compiler->compileString('<html {{ $foo }} @lang(\'foo\')>'));
    }
}

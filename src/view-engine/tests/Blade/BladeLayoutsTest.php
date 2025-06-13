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
/**
 * @internal
 * @coversNothing
 */
class BladeLayoutsTest extends AbstractBladeTestCase
{
    public function testAppendSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->appendSection(); ?>', $this->compiler->compileString('@append'));
    }

    public function testEndSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(); ?>', $this->compiler->compileString('@endsection'));
    }

    public function testExtendsAreCompiled()
    {
        $string = '@extends(\'foo\')
test';
        $expected = "test\n" . '<?php echo $__env->make(\'foo\', \Hyperf\Collection\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));

        $string = '@extends(name(foo))' . "\n" . 'test';
        $expected = "test\n" . '<?php echo $__env->make(name(foo), \Hyperf\Collection\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testSequentialCompileStringCalls()
    {
        $string = '@extends(\'foo\')
test';
        $expected = "test\n" . '<?php echo $__env->make(\'foo\', \Hyperf\Collection\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));

        // use the same compiler instance to compile another template with @extends directive
        $string = "@extends(name(foo))\ntest";
        $expected = "test\n" . '<?php echo $__env->make(name(foo), \Hyperf\Collection\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testOverwriteSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(true); ?>', $this->compiler->compileString('@overwrite'));
    }

    public function testStartSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->startSection(\'foo\'); ?>', $this->compiler->compileString('@section(\'foo\')'));
        $this->assertSame('<?php $__env->startSection(\'issue#18317 :))\'); ?>', $this->compiler->compileString('@section(\'issue#18317 :))\')'));
        $this->assertSame('<?php $__env->startSection(name(foo)); ?>', $this->compiler->compileString('@section(name(foo))'));
    }

    public function testStopSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(); ?>', $this->compiler->compileString('@stop'));
    }

    public function testShowsAreCompiled()
    {
        $this->assertSame('<?php echo $__env->yieldSection(); ?>', $this->compiler->compileString('@show'));
    }

    public function testYieldsAreCompiled()
    {
        $this->assertSame('<?php echo $__env->yieldContent(\'foo\'); ?>', $this->compiler->compileString('@yield(\'foo\')'));
        $this->assertSame('<?php echo $__env->yieldContent(\'foo\', \'bar\'); ?>', $this->compiler->compileString('@yield(\'foo\', \'bar\')'));
        $this->assertSame('<?php echo $__env->yieldContent(name(foo)); ?>', $this->compiler->compileString('@yield(name(foo))'));
    }
}

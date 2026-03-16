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
class BladeStacksTest extends AbstractBladeTestCase
{
    public function testStackIsCompiled()
    {
        $string = '@stack(\'foo\')';
        $expected = '<?php echo $__env->yieldPushContent(\'foo\'); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));

        $string = '@stack(\'foo))\')';
        $expected = '<?php echo $__env->yieldPushContent(\'foo))\'); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPushIsCompiled()
    {
        $string = '@push(\'foo\')
test
@endpush';
        $expected = '<?php $__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPushIsCompiledWithParenthesis()
    {
        $string = '@push(\'foo):))\')
test
@endpush';
        $expected = '<?php $__env->startPush(\'foo):))\'); ?>
test
<?php $__env->stopPush(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}

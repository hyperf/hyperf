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

/**
 * @internal
 * @coversNothing
 */
class BladeCommentsTest extends AbstractBladeTestCase
{
    public function testCommentsAreCompiled()
    {
        $string = '{{--this is a comment--}}';
        $this->assertEmpty($this->compiler->compileString($string));

        $string = '{{--
this is a comment
--}}';
        $this->assertEmpty($this->compiler->compileString($string));

        $string = sprintf('{{-- this is an %s long comment --}}', str_repeat('extremely ', 1000));
        $this->assertEmpty($this->compiler->compileString($string));
    }

    public function testBladeCodeInsideCommentsIsNotCompiled()
    {
        $string = '{{-- @foreach() --}}';

        $this->assertEmpty($this->compiler->compileString($string));
    }
}

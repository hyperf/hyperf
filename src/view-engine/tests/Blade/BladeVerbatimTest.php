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
class BladeVerbatimTest extends AbstractBladeTestCase
{
    public function testVerbatimBlocksAreCompiled()
    {
        $string = '@verbatim {{ $a }} @if($b) {{ $b }} @endif @endverbatim';
        $expected = ' {{ $a }} @if($b) {{ $b }} @endif ';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testVerbatimBlocksWithMultipleLinesAreCompiled()
    {
        $string = 'Some text
@verbatim
    {{ $a }}
    @if($b)
        {{ $b }}
    @endif
@endverbatim';
        $expected = 'Some text

    {{ $a }}
    @if($b)
        {{ $b }}
    @endif
';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testMultipleVerbatimBlocksAreCompiled()
    {
        $string = '@verbatim {{ $a }} @endverbatim {{ $b }} @verbatim {{ $c }} @endverbatim';
        $expected = ' {{ $a }}  <?php echo \Hyperf\ViewEngine\T::e($b); ?>  {{ $c }} ';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testRawBlocksAreRenderedInTheRightOrder()
    {
        $string = '@php echo "#1"; @endphp @verbatim {{ #2 }} @endverbatim @verbatim {{ #3 }} @endverbatim @php echo "#4"; @endphp';

        $expected = '<?php echo "#1"; ?>  {{ #2 }}   {{ #3 }}  <?php echo "#4"; ?>';

        $this->assertSame($expected, $this->compiler->compileString($string));
    }

    public function testMultilineTemplatesWithRawBlocksAreRenderedInTheRightOrder()
    {
        $string = '{{ $first }}
@php
    echo $second;
@endphp
@if ($conditional)
    {{ $third }}
@endif
@include("users")
@verbatim
    {{ $fourth }} @include("test")
@endverbatim
@php echo $fifth; @endphp';

        $expected = '<?php echo \Hyperf\ViewEngine\T::e($first); ?>

<?php
    echo $second;
?>
<?php if($conditional): ?>
    <?php echo \Hyperf\ViewEngine\T::e($third); ?>

<?php endif; ?>
<?php echo $__env->make("users", \Hyperf\Utils\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>

    {{ $fourth }} @include("test")

<?php echo $fifth; ?>';

        $this->assertSame($expected, $this->compiler->compileString($string));
    }

    public function testRawBlocksDontGetMixedUpWhenSomeAreRemovedByBladeComments()
    {
        $string = '{{-- @verbatim Block #1 @endverbatim --}} @php "Block #2" @endphp';
        $expected = ' <?php "Block #2" ?>';

        $this->assertSame($expected, $this->compiler->compileString($string));
    }
}

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
class BladeConditionalsTest extends AbstractBladeTestCase
{
    public function testElseAuthStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@elseauth("standard")
wheeze
@endauth';
        $expected = '<?php if(auth()->guard("api")->check()): ?>
breeze
<?php elseif(auth()->guard("standard")->check()): ?>
wheeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPlainElseAuthStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@elseauth
wheeze
@endauth';
        $expected = '<?php if(auth()->guard("api")->check()): ?>
breeze
<?php elseif(auth()->guard()->check()): ?>
wheeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testIfStatementsAreCompiled()
    {
        $string = '@guest("api")
breeze
@elseguest("standard")
wheeze
@endguest';
        $expected = '<?php if(auth()->guard("api")->guest()): ?>
breeze
<?php elseif(auth()->guard("standard")->guest()): ?>
wheeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testElseIfStatementsAreCompiled()
    {
        $string = '@if(name(foo(bar)))
breeze
@elseif(boom(breeze))
boom
@endif';
        $expected = '<?php if(name(foo(bar))): ?>
breeze
<?php elseif(boom(breeze)): ?>
boom
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testElseStatementsAreCompiled()
    {
        $string = '@if (name(foo(bar)))
breeze
@else
boom
@endif';
        $expected = '<?php if(name(foo(bar))): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testIfAuthStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@endauth';
        $expected = '<?php if(auth()->guard("api")->check()): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPlainIfAuthStatementsAreCompiled()
    {
        $string = '@auth
breeze
@endauth';
        $expected = '<?php if(auth()->guard()->check()): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testIfEmptyStatementsAreCompiled()
    {
        $string = '@empty ($test)
breeze
@endempty';
        $expected = '<?php if(empty($test)): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testIfGuestStatementsAreCompiled()
    {
        $string = '@guest("api")
breeze
@endguest';
        $expected = '<?php if(auth()->guard("api")->guest()): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testIfIssetStatementsAreCompiled()
    {
        $string = '@isset ($test)
breeze
@endisset';
        $expected = '<?php if(isset($test)): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testSwitchStatementsAreCompiled()
    {
        $string = '@switch(true)
@case(1)
foo

@case(2)
bar
@endswitch

foo

@switch(true)
@case(1)
foo

@case(2)
bar
@endswitch';
        $expected = '<?php switch(true):
case (1): ?>
foo

<?php case (2): ?>
bar
<?php endswitch; ?>

foo

<?php switch(true):
case (1): ?>
foo

<?php case (2): ?>
bar
<?php endswitch; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testEnvStatementsAreCompiled()
    {
        $string = "@env('staging')
breeze
@else
boom
@endenv";
        $expected = '<?php if(\in_array($__env->getContainer()->get(Hyperf\Contract\ConfigInterface::class)->get(\'app_env\'), [\'staging\'])): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testEnvStatementsWithArrayParamAreCompiled()
    {
        $string = "@env(['staging', 'production'])
breeze
@else
boom
@endenv";
        $expected = '<?php if(\in_array($__env->getContainer()->get(Hyperf\Contract\ConfigInterface::class)->get(\'app_env\'), [\'staging\', \'production\'])): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testProductionStatementsAreCompiled()
    {
        $string = '@production
breeze
@else
boom
@endproduction';
        $expected = '<?php if(\in_array($__env->getContainer()->get(Hyperf\Contract\ConfigInterface::class)->get(\'app_env\'), [\'prod\', \'production\'])): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testHasSectionStatementsAreCompiled()
    {
        $string = '@hasSection("section")
breeze
@endif';
        $expected = '<?php if (! empty(trim($__env->yieldContent("section")))): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testSectionMissingStatementsAreCompiled()
    {
        $string = '@sectionMissing("section")
breeze
@endif';
        $expected = '<?php if (empty(trim($__env->yieldContent("section")))): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testUnlessStatementsAreCompiled()
    {
        $string = '@unless (name(foo(bar)))
breeze
@endunless';
        $expected = '<?php if (! (name(foo(bar)))): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}

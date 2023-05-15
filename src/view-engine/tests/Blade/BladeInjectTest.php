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
class BladeInjectTest extends AbstractBladeTestCase
{
    public function testDependenciesInjectedAsStringsAreCompiled()
    {
        $string = "Foo @inject('baz', 'SomeNamespace\\SomeClass') bar";
        $expected = "Foo <?php \$baz = \\Hyperf\\ViewEngine\\T::inject('SomeNamespace\\SomeClass'); ?> bar";
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testDependenciesInjectedAsStringsAreCompiledWhenInjectedWithDoubleQuotes()
    {
        $string = 'Foo @inject("baz", "SomeNamespace\SomeClass") bar';
        $expected = 'Foo <?php $baz = \Hyperf\ViewEngine\T::inject("SomeNamespace\SomeClass"); ?> bar';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testDependenciesAreCompiled()
    {
        $string = "Foo @inject('baz', SomeNamespace\\SomeClass::class) bar";
        $expected = 'Foo <?php $baz = \Hyperf\ViewEngine\T::inject(SomeNamespace\\SomeClass::class); ?> bar';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testDependenciesAreCompiledWithDoubleQuotes()
    {
        $string = 'Foo @inject("baz", SomeNamespace\SomeClass::class) bar';
        $expected = 'Foo <?php $baz = \Hyperf\ViewEngine\T::inject(SomeNamespace\\SomeClass::class); ?> bar';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}

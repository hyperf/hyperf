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

use Hyperf\ViewEngine\Compiler\BladeCompiler;
use Hyperf\ViewEngine\Compiler\ComponentTagCompiler;
use HyperfTest\ViewEngine\Stub\ContainerStub;

/**
 * @internal
 * @coversNothing
 */
class BladeComponentTagCompilerTest extends AbstractBladeTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        ContainerStub::mockContainer();
    }

    public function testSlotsCanBeCompiled()
    {
        $result = $this->compiler()->compileSlots('<x-slot name="foo">
</x-slot>');

        $this->assertSame("@slot('foo') \n" . ' @endslot', trim($result));
    }

    public function testDynamicSlotsCanBeCompiled()
    {
        $result = $this->compiler()->compileSlots('<x-slot :name="$foo">
</x-slot>');

        $this->assertSame("@slot(\$foo) \n" . ' @endslot', trim($result));
    }

    public function testDynamicSlotsCanBeCompiledWithKeyOfObjects()
    {
        $result = $this->compiler()->compileSlots('<x-slot :name="$foo->name">
</x-slot>');

        $this->assertSame("@slot(\$foo->name) \n" . ' @endslot', trim($result));
    }

    protected function compiler(array $aliases = [], array $namespaces = [], ?BladeCompiler $blade = null): ComponentTagCompiler
    {
        return new ComponentTagCompiler(
            $aliases,
            $namespaces,
            $blade
        );
    }
}

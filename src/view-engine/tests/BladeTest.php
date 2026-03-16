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

namespace HyperfTest\ViewEngine;

use Hyperf\Context\ApplicationContext;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\FinderInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Hyperf\ViewEngine\Factory\FinderFactory;
use Hyperf\ViewEngine\HyperfViewEngine;
use HyperfTest\ViewEngine\Stub\ContainerStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\ViewEngine\view;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class BladeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ContainerStub::mockContainer();

        // vendor 下的命令空间
        if (! file_exists(__DIR__ . '/storage/view/vendor/admin/simple_4.blade.php')) {
            @mkdir(__DIR__ . '/storage/view/vendor');
            @mkdir(__DIR__ . '/storage/view/vendor/admin_custom');
            @mkdir(__DIR__ . '/storage/view/vendor/admin_config');
            file_put_contents(__DIR__ . '/storage/view/vendor/admin_custom/simple_4.blade.php', 'from_vendor');
            file_put_contents(__DIR__ . '/storage/view/vendor/admin_config/simple_4.blade.php', 'from_vendor');
        }
    }

    public function testRegisterComponents()
    {
        $this->assertSame('success', trim((string) view('simple_8', ['message' => 'success'])));
        $this->assertSame('success', trim((string) view('simple_9', ['message' => 'success'])));
    }

    public function testRegisterNamespace()
    {
        $this->assertSame('from_admin', trim((string) view('admin_config::simple_3')));
        $this->assertSame('from_vendor', trim((string) view('admin_config::simple_4')));
    }

    public function testViewFunction()
    {
        $this->assertInstanceOf(FactoryInterface::class, view());
        $this->assertInstanceOf(ViewInterface::class, view('index'));
    }

    public function testHyperfEngine()
    {
        $engine = new HyperfViewEngine();

        $this->assertSame('<h1>fangx/view</h1>', $engine->render('index', [], []));
        $this->assertSame('<h1>fangx</h1>', $engine->render('home', ['user' => 'fangx'], []));
    }

    public function testRender()
    {
        $this->assertSame('<h1>fangx/view</h1>', trim((string) view('index')));
        $this->assertSame('<h1>fangx</h1>', trim((string) view('home', ['user' => 'fangx'])));
        // *.php
        $this->assertSame('fangx', trim((string) view('simple_1')));
        // *.html
        $this->assertSame('fangx', trim((string) view('simple_2')));
        // @extends & @yield & @section..@stop
        $this->assertSame('yield-content', trim((string) view('simple_5')));
        // @if..@else..@endif
        $this->assertSame('fangx', trim((string) view('simple_6')));
        // @{{ name }}
        $this->assertSame('{{ name }}', trim((string) view('simple_7')));
        // @json()
        $this->assertSame('{"email":"nfangxu@gmail.com","name":"fangx"}', trim((string) view('simple_10')));
    }

    public function testUseNamespace()
    {
        $finder = ApplicationContext::getContainer()->get(FinderInterface::class);
        $factory = new FinderFactory();
        $factory->addNamespace($finder, 'admin_custom', __DIR__ . '/admin');

        $this->assertSame('from_admin', trim((string) view('admin_custom::simple_3')));
        $this->assertSame('from_vendor', trim((string) view('admin_custom::simple_4')));
    }

    public function testComponent()
    {
        $this->assertSame('success', trim((string) view('simple_8', ['message' => 'success'])));
        $this->assertSame('success', trim((string) view('simple_9', ['message' => 'success'])));
    }

    public function testDynamicComponent()
    {
        $this->assertSame('ok', trim((string) view('simple_11', ['componentName' => 'alert', 'message' => 'ok'])));
    }

    public function testComponentAutoload()
    {
        $this->assertSame('success', trim((string) view('simple_12', ['message' => 'success'])));
    }

    public function testComponentMergeAttribute()
    {
        $this->assertSame('<div class="alert alert-error mb4" style="height:50px">success</div>', trim((string) view('simple_13', ['message' => 'success'])));
        $this->assertSame('<div class="alert alert-error mb4" style="background-color:red; height:50px">success</div>', trim((string) view('simple_14', ['message' => 'success'])));
    }
}

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
namespace HyperfTest\Di;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Di\ExceptionStub\DemoInjectException;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\DemoInject;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class InjectTest extends TestCase
{
    protected function tearDown()
    {
        AspectCollector::clear();
        AnnotationCollector::clear();
        Mockery::close();
        BetterReflectionManager::clear();
    }

    public function testInject()
    {
        $this->getContainer();
        $ast = new Ast();
        $code = $ast->proxy(DemoInject::class);
        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file = $dir . 'DemoInject.proxy.php', $code);
        require $file;

        $demoInject = new DemoInject();
        $this->assertSame(Demo::class, get_class($demoInject->getDemo()));
        $this->assertSame(null, $demoInject->getDemo1());
    }

    public function testInjectException()
    {
        $this->getContainer();
        $ast = new Ast();
        $code = $ast->proxy(DemoInjectException::class);
        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file = $dir . 'DemoInjectException.proxy.php', $code);
        require $file;

        try {
            new DemoInjectException();
        } catch (\Exception $e) {
            $this->assertSame(true, $e instanceof NotFoundException);
        }
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub']);

        $scaner = new Scanner($loader = Mockery::mock(ClassLoader::class), new ScanConfig(false, '/'));
        $reader = new AnnotationReader();
        $scaner->collect($reader, BetterReflectionManager::reflectClass(DemoInject::class));
        $scaner->collect($reader, BetterReflectionManager::reflectClass(DemoInjectException::class));

        $container->shouldReceive('get')->with(Demo::class)->andReturn(new Demo());
        $container->shouldReceive('has')->with(Demo::class)->andReturn(true);
        $container->shouldReceive('has')->with('HyperfTest\Di\ExceptionStub\Demo1')->andReturn(false);
        return $container;
    }
}

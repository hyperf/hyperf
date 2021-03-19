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
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Di\ExceptionStub\DemoInjectException;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\DemoInject;
use HyperfTest\Di\Stub\EmptyVarValue;
use HyperfTest\Di\Stub\Inject\Bar;
use HyperfTest\Di\Stub\Inject\Foo;
use HyperfTest\Di\Stub\Inject\Foo2Trait;
use HyperfTest\Di\Stub\Inject\FooTrait;
use HyperfTest\Di\Stub\Inject\Origin2Class;
use HyperfTest\Di\Stub\Inject\Origin3Class;
use HyperfTest\Di\Stub\Inject\Origin4Class;
use HyperfTest\Di\Stub\Inject\Origin5Class;
use HyperfTest\Di\Stub\Inject\OriginClass;
use HyperfTest\Di\Stub\Inject\Parent2Class;
use HyperfTest\Di\Stub\Inject\Parent3Class;
use HyperfTest\Di\Stub\Inject\ParentClass;
use HyperfTest\Di\Stub\Inject\Tar;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class InjectTest extends TestCase
{
    protected function tearDown(): void
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

    public function testInjectWithTraitAndParent()
    {
        $this->getContainer();
        $ast = new Ast();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            OriginClass::class,
        ];

        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }

        foreach ($classes as $class) {
            $code = $ast->proxy($class);
            $id = md5($class);
            file_put_contents($file = $dir . $id . '.proxy.php', $code);
            require_once $file;
        }

        $origin = new OriginClass();
        $this->assertInstanceOf(Tar::class, $origin->getFoo());
    }

    public function testInjectTraitAndParent()
    {
        $this->getContainer();
        $ast = new Ast();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            Origin2Class::class,
        ];

        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }

        foreach ($classes as $class) {
            $code = $ast->proxy($class);
            $id = md5($class);
            file_put_contents($file = $dir . $id . '.proxy.php', $code);
            require_once $file;
        }

        $origin = new Origin2Class();
        $this->assertInstanceOf(Bar::class, $origin->getFoo());
    }

    public function testInjectParent()
    {
        $this->getContainer();
        $ast = new Ast();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            Origin3Class::class,
        ];

        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }

        foreach ($classes as $class) {
            $code = $ast->proxy($class);
            $id = md5($class);
            file_put_contents($file = $dir . $id . '.proxy.php', $code);
            require_once $file;
        }

        $origin = new Origin3Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
    }

    public function testInject2Trait()
    {
        $this->getContainer();
        $ast = new Ast();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            Origin4Class::class,
        ];

        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }

        foreach ($classes as $class) {
            $code = $ast->proxy($class);
            $id = md5($class);
            file_put_contents($file = $dir . $id . '.proxy.php', $code);
            require_once $file;
        }

        $origin = new Origin4Class();
        $this->assertInstanceOf(Tar::class, $origin->getFoo());
    }

    public function testInjectParentParent()
    {
        $this->getContainer();
        $ast = new Ast();
        $classes = [
            Parent2Class::class,
            Origin5Class::class,
        ];

        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }

        foreach ($classes as $class) {
            $code = $ast->proxy($class);
            $id = md5($class);
            file_put_contents($file = $dir . $id . '.proxy.php', $code);
            require_once $file;
        }

        $origin = new Origin3Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
    }

    public function testInjectParentNoRunParent()
    {
        $this->getContainer();
        $ast = new Ast();
        $classes = [
            Parent3Class::class,
            ParentClass::class,
        ];

        if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
            mkdir($dir, 0777, true);
        }

        foreach ($classes as $class) {
            $code = $ast->proxy($class);
            $id = md5($class);
            file_put_contents($file = $dir . $id . '.proxy.php', $code);
            require_once $file;
        }

        $origin = new Parent3Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
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

    public function testInjectNotInitReflector()
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('The @Inject value is invalid for HyperfTest\Di\Stub\EmptyVarValue->demo. Because The class reflector object does not init yet');

        $inject = new Inject();
        $inject->collectProperty(EmptyVarValue::class, 'demo');
    }

    public function testInjectEmptyVar()
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('The @Inject value is invalid for HyperfTest\Di\Stub\EmptyVarValue->demo');

        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub']);

        $inject = new Inject();
        $inject->collectProperty(EmptyVarValue::class, 'demo');
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub']);

        $scaner = new Scanner($loader = Mockery::mock(ClassLoader::class), new ScanConfig(false, '/'));
        $reader = new AnnotationReader();

        $classes = [
            DemoInject::class,
            DemoInjectException::class,
            OriginClass::class,
            ParentClass::class,
            FooTrait::class,
            Origin2Class::class,
            Origin3Class::class,
            Foo2Trait::class,
            Origin4Class::class,
            Origin5Class::class,
            Parent2Class::class,
            Parent3Class::class,
        ];

        foreach ($classes as $class) {
            $scaner->collect($reader, BetterReflectionManager::reflectClass($class));
        }

        $classes = [
            Bar::class,
            Foo::class,
            Tar::class,
            Demo::class,
        ];

        foreach ($classes as $class) {
            $container->shouldReceive('has')->with($class)->andReturn(true);
            $container->shouldReceive('get')->with($class)->andReturn(new $class());
        }

        $container->shouldReceive('has')->with('HyperfTest\Di\ExceptionStub\Demo1')->andReturn(false);
        return $container;
    }
}

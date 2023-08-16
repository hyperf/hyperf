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

use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use HyperfTest\Di\ExceptionStub\DemoInjectException;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\DemoInject;
use HyperfTest\Di\Stub\EmptyVarValue;
use HyperfTest\Di\Stub\Inject\Bar;
use HyperfTest\Di\Stub\Inject\Foo;
use HyperfTest\Di\Stub\Inject\Foo2Trait;
use HyperfTest\Di\Stub\Inject\Foo3Trait;
use HyperfTest\Di\Stub\Inject\FooTrait;
use HyperfTest\Di\Stub\Inject\Origin2Class;
use HyperfTest\Di\Stub\Inject\Origin3Class;
use HyperfTest\Di\Stub\Inject\Origin4Class;
use HyperfTest\Di\Stub\Inject\Origin5Class;
use HyperfTest\Di\Stub\Inject\Origin6Class;
use HyperfTest\Di\Stub\Inject\Origin7Class;
use HyperfTest\Di\Stub\Inject\OriginClass;
use HyperfTest\Di\Stub\Inject\Parent2Class;
use HyperfTest\Di\Stub\Inject\Parent3Class;
use HyperfTest\Di\Stub\Inject\Parent4Class;
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
        ReflectionManager::clear();
    }

    /**
     * @group NonCoroutine
     */
    public function testInject()
    {
        $this->getContainer();
        require_once BASE_PATH . '/runtime/container/proxy/' . md5(DemoInject::class) . '.proxy.php';

        $demoInject = new DemoInject();
        $this->assertSame(Demo::class, get_class($demoInject->getDemo()));
        $this->assertSame(null, $demoInject->getDemo1());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectWithTraitAndParent()
    {
        $this->getContainer();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            OriginClass::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new OriginClass();
        $this->assertInstanceOf(Tar::class, $origin->getFoo());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectTraitAndParent()
    {
        $this->markTestSkipped('@var does not works as expect.');
        $this->getContainer();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            Origin2Class::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Origin2Class();
        $this->assertInstanceOf(Bar::class, $origin->getFoo());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectParent()
    {
        $this->getContainer();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            Origin3Class::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Origin3Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
    }

    /**
     * @group NonCoroutine
     */
    public function testInject2Trait()
    {
        $this->markTestSkipped('@var does not works as expect.');

        $this->getContainer();
        $classes = [
            ParentClass::class,
            FooTrait::class,
            Origin4Class::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Origin4Class();
        $this->assertInstanceOf(Tar::class, $origin->getFoo());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectTraitNesting()
    {
        $this->getContainer();
        $classes = [
            FooTrait::class,
            Foo3Trait::class,
            Origin7Class::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Origin7Class();
        $this->assertInstanceOf(Bar::class, $origin->getFoo());
        $this->assertInstanceOf(Bar::class, $origin->getBar());
        $this->assertSame('foo3', $origin->getValue());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectParentParent()
    {
        $this->getContainer();
        $classes = [
            Parent2Class::class,
            Origin5Class::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Origin3Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectParentNoRunParent()
    {
        $this->getContainer();
        $classes = [
            Parent3Class::class,
            ParentClass::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Parent3Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
    }

    /**
     * @group NonCoroutine
     */
    public function testInjectParentPrivateProperty()
    {
        $this->getContainer();
        $classes = [
            Parent4Class::class,
            Origin6Class::class,
        ];

        foreach ($classes as $class) {
            require_once BASE_PATH . '/runtime/container/proxy/' . md5($class) . '.proxy.php';
        }

        $origin = new Origin6Class();
        $this->assertInstanceOf(Foo::class, $origin->getFoo());
        $this->assertInstanceOf(Bar::class, $origin->getBar());
    }

    public function testInjectException()
    {
        try {
            $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
            $reader = new AnnotationReader();
            $scanner->collect($reader, ReflectionManager::reflectClass(DemoInjectException::class));
        } catch (Exception $e) {
            $this->assertSame('The @var annotation on HyperfTest\Di\ExceptionStub\DemoInjectException::demo contains a non existent class "Demo1". Did you maybe forget to add a "use" statement for this annotation?', $e->getMessage());
            $this->assertSame(true, $e instanceof AnnotationException);
        }
    }

    public function testInjectEmptyVar()
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('The @Inject value is invalid for HyperfTest\Di\Stub\EmptyVarValue->demo');

        $inject = new Inject();
        $inject->collectProperty(EmptyVarValue::class, 'demo');
    }

    protected function getContainer(array $classes = [])
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        $path = BASE_PATH . '/runtime/scan.cache';

        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('The process fork failed');
        }
        if ($pid === 0) {
            $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
            $reader = new AnnotationReader();

            if (empty($classes)) {
                $classes = [
                    DemoInject::class,
                    OriginClass::class,
                    ParentClass::class,
                    FooTrait::class,
                    Origin2Class::class,
                    Origin3Class::class,
                    Foo2Trait::class,
                    Foo3Trait::class,
                    Origin4Class::class,
                    Origin5Class::class,
                    Parent2Class::class,
                    Parent3Class::class,
                    Parent4Class::class,
                    Origin6Class::class,
                    Origin7Class::class,
                ];
            }

            foreach ($classes as $class) {
                $scanner->collect($reader, ReflectionManager::reflectClass($class));
            }

            file_put_contents($path, AnnotationCollector::serialize());

            $ast = new Ast();
            if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
                mkdir($dir, 0777, true);
            }
            foreach ($classes as $class) {
                $code = $ast->proxy($class);
                $id = md5($class);
                file_put_contents($dir . $id . '.proxy.php', $code);
            }

            exit;
        }

        pcntl_wait($status);

        AnnotationCollector::deserialize(file_get_contents($path));

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

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di;


use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\DemoInject;
use HyperfTest\Di\Stub\DemoInjectException;
use PhpDocReader\AnnotationException;
use PHPUnit\Framework\TestCase;

class InjectTest extends TestCase
{
    public function testInject()
    {
        $container = new Container(new DefinitionSource([], ['./Stub'], new Scanner()));
        $demoInject = $container->get(DemoInject::class);
        $this->assertSame(Demo::class, get_class($demoInject->getDemo()));
        $this->assertSame(null, $demoInject->getDemo1());
    }

    public function testInjectException()
    {

        try {
            $container = new Container(new DefinitionSource([], ['./Stub','./ExceptionStub'], new Scanner()));
            $container->get(DemoInjectException::class);
        } catch (\Exception $e) {
            $this->assertSame(true, $e instanceof AnnotationException);
        }
    }

    protected function tearDown()
    {
        AspectCollector::clear();
        AnnotationCollector::clear();
    }

}

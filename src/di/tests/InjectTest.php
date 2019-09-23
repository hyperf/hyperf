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

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Definition\ScanConfig;
use HyperfTest\Di\ExceptionStub\DemoInjectException;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\DemoInject;
use PhpDocReader\AnnotationException;
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
    }

    public function testInject()
    {
        $container = new Container(new DefinitionSource([], new ScanConfig([__DIR__ . '/Stub'])));
        $demoInject = $container->get(DemoInject::class);
        $this->assertSame(Demo::class, get_class($demoInject->getDemo()));
        $this->assertSame(null, $demoInject->getDemo1());
    }

    public function testInjectException()
    {
        try {
            $container = new Container(new DefinitionSource([], new ScanConfig([__DIR__ . '/Stub', __DIR__ . '/ExceptionStub'])));
            $container->get(DemoInjectException::class);
        } catch (\Exception $e) {
            $this->assertSame(true, $e instanceof AnnotationException);
        }
    }
}

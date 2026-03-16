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

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\MetadataCacheCollector;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\DemoAnnotation;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MetadataCollectorTest extends TestCase
{
    protected function tearDown(): void
    {
        AspectCollector::clear();
        AnnotationCollector::clear();
    }

    public function testMetadataCollectorCache()
    {
        $annotation = DemoAnnotation::class;
        $id = uniqid();

        AnnotationCollector::collectClass('Demo', $annotation, new DemoAnnotation($id));
        $collector = AnnotationCollector::list();

        $cacher = new MetadataCacheCollector([
            AnnotationCollector::class,
            AspectCollector::class,
        ]);

        $string = $cacher->serialize();
        AnnotationCollector::clear();
        $cacher->unserialize($string);

        $collector2 = AnnotationCollector::list();

        $this->assertEquals($collector, $collector2);
    }

    public function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
                'cacheable' => [
                    AnnotationCollector::class,
                    AspectCollector::class,
                ],
            ],
        ]));

        return $container;
    }
}

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

namespace HyperfTest\ModelListener;

use Hyperf\ModelListener\Annotation\ModelListener;
use Hyperf\ModelListener\Collector\ListenerCollector;
use HyperfTest\ModelListener\Stub\ModelStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AnnotationTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        ListenerCollector::clearListeners();
    }

    public function testAnnotationCollect()
    {
        $annotation = new ModelListener([ModelStub::class]);
        $annotation->collectClass('Foo');

        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel(ModelStub::class));
    }

    public function testAnnotationCollectAssocArray()
    {
        $annotation = new ModelListener([ModelStub::class]);
        $annotation->collectClass('Foo');
        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel(ModelStub::class));
    }

    public function testAnnotationCollectArray()
    {
        $annotation = new ModelListener([ModelStub::class, 'ModelStub']);
        $annotation->collectClass('Foo');
        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel(ModelStub::class));
        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel('ModelStub'));
    }
}

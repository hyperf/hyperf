<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace HyperfTest\ModelListener;

use Hyperf\ModelListener\Annotation\ModelListener;
use Hyperf\ModelListener\Collector\ListenerCollector;
use HyperfTest\ModelListener\Stub\ModelStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        ListenerCollector::clearListeners();
    }

    public function testAnnotationCollect()
    {
        $annotation = new ModelListener(['value' => ModelStub::class]);
        $annotation->collectClass('Foo');

        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel(ModelStub::class));
    }

    public function testAnnotationCollectAssocArray()
    {
        $annotation = new ModelListener(['models' => [ModelStub::class]]);
        $annotation->collectClass('Foo');
        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel(ModelStub::class));
    }

    public function testAnnotationCollectArray()
    {
        $annotation = new ModelListener(['value' => [ModelStub::class, 'ModelStub']]);
        $annotation->collectClass('Foo');
        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel(ModelStub::class));
        $this->assertSame(['Foo'], ListenerCollector::getListenersForModel('ModelStub'));
    }
}

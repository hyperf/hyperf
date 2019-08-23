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

namespace HyperfTest\ModelObserver;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Register;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\ModelObserver\Collector\ObserverCollector;
use Hyperf\ModelObserver\Listener\ModelEventListener;
use HyperfTest\ModelObserver\Stub\ModelObserverStub;
use HyperfTest\ModelObserver\Stub\ModelStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ModelObserverTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        ObserverCollector::clearObservables();
    }

    public function testHandleModelObserver()
    {
        $container = $this->getContainer();
        $listenerProvider = new ListenerProvider();
        $listenerProvider->on(Event::class, [new ModelEventListener($container), 'process']);

        Register::setEventDispatcher(new EventDispatcher($listenerProvider));

        $model = $this->getMockBuilder(ModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps'])->getMock();
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['foo' => 'bar'])->andReturn(1);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        ObserverCollector::register(get_class($model), ModelObserverStub::class);

        $model->id = 1;
        $model->syncOriginal();
        $model->foo = 'foo';
        $model->exists = true;

        $this->assertTrue($model->save());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(ModelObserverStub::class)->andReturn(new ModelObserverStub());

        return $container;
    }
}

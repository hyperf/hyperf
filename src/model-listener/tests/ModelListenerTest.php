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

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Register;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\ModelListener\Collector\ListenerCollector;
use Hyperf\ModelListener\Listener\ModelEventListener;
use HyperfTest\ModelListener\Stub\ModelListenerStub;
use HyperfTest\ModelListener\Stub\ModelStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ModelListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        ListenerCollector::clearListeners();
    }

    public function testHandleModelListener()
    {
        $container = $this->getContainer();
        $listenerProvider = new ListenerProvider();
        $listenerProvider->on(Event::class, [new ModelEventListener($container), 'process']);

        Register::setEventDispatcher(new EventDispatcher($listenerProvider));

        $model = $this->getMockBuilder(ModelStub::class)->onlyMethods(['newModelQuery', 'updateTimestamps'])->getMock();
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['foo' => 'bar'])->andReturn(1);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        ListenerCollector::register(get_class($model), ModelListenerStub::class);

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
        $container->shouldReceive('get')->with(ModelListenerStub::class)->andReturn(new ModelListenerStub());

        return $container;
    }
}

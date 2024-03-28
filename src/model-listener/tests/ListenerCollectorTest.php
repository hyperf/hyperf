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

use Hyperf\ModelListener\Collector\ListenerCollector;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ListenerCollectorTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        ListenerCollector::clearListeners();
    }

    public function testRegisterObserver()
    {
        $class = 'HyperfTest\ModelListener\Stub\ModelStub';
        ListenerCollector::register($class, 'ObserverClass');
        $this->assertSame(['ObserverClass'], ListenerCollector::getListenersForModel($class));
    }

    public function testRegisterMoreThanOneObserver()
    {
        $class = 'HyperfTest\ModelListener\Stub\ModelStub';
        ListenerCollector::register($class, 'ObserverClass');
        ListenerCollector::register($class, 'ObserverClass2');
        ListenerCollector::register($class, 'ObserverClass3');
        $this->assertSame(['ObserverClass', 'ObserverClass2', 'ObserverClass3'], ListenerCollector::getListenersForModel($class));
    }

    public function testClearObservables()
    {
        $class = 'HyperfTest\ModelListener\Stub\ModelStub';
        ListenerCollector::register($class, 'ObserverClass');

        ListenerCollector::clearListeners();

        $this->assertSame([], ListenerCollector::getListenersForModel($class));
    }
}

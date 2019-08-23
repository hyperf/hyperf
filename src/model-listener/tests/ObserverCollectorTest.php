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

namespace HyperfTest\ModelListener;

use Hyperf\ModelListener\Collector\ObserverCollector;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ObserverCollectorTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        ObserverCollector::clearObservables();
    }

    public function testRegisterObserver()
    {
        $class = 'HyperfTest\ModelListener\Stub\ModelStub';
        ObserverCollector::register($class, 'ObserverClass');
        $this->assertSame(['ObserverClass'], ObserverCollector::getObservables($class));
    }

    public function testRegisterMoreThanOneObserver()
    {
        $class = 'HyperfTest\ModelListener\Stub\ModelStub';
        ObserverCollector::register($class, 'ObserverClass');
        ObserverCollector::register($class, 'ObserverClass2');
        ObserverCollector::register($class, 'ObserverClass3');
        $this->assertSame(['ObserverClass', 'ObserverClass2', 'ObserverClass3'], ObserverCollector::getObservables($class));
    }

    public function testClearObservables()
    {
        $class = 'HyperfTest\ModelListener\Stub\ModelStub';
        ObserverCollector::register($class, 'ObserverClass');

        ObserverCollector::clearObservables();

        $this->assertSame([], ObserverCollector::getObservables($class));
    }
}

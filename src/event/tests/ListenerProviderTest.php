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

namespace HyperfTest\Event;

use Hyperf\Event\ListenerProvider;
use HyperfTest\Event\Event\Alpha;
use HyperfTest\Event\Event\Beta;
use HyperfTest\Event\Listener\AlphaListener;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ListenerProviderTest extends TestCase
{
    public function testListenNotExistEvent()
    {
        $provider = new ListenerProvider();
        $provider->on(Alpha::class, [new AlphaListener(), 'process']);
        $provider->on('NotExistEvent', [new AlphaListener(), 'process']);

        $it = $provider->getListenersForEvent(new Alpha());
        [$class, $method] = $it->current();
        $this->assertInstanceOf(AlphaListener::class, $class);
        $this->assertSame('process', $method);
        $this->assertNull($it->next());

        $it = $provider->getListenersForEvent(new Beta());
        $this->assertNull($it->current());
    }
}

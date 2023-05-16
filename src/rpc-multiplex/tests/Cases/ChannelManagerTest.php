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
namespace HyperfTest\RpcMultiplex\Cases;

use Hyperf\Coroutine\Coroutine;
use Multiplex\ChannelManager;

/**
 * @internal
 * @coversNothing
 */
class ChannelManagerTest extends AbstractTestCase
{
    public function testChannelManagerFlush()
    {
        $manager = new ChannelManager();
        $chan = $manager->get(1, true);
        $chan->push(true);
        $chan2 = $manager->get(2, true);
        $chan2->push(true);

        Coroutine::create(function () use ($manager, $chan, $chan2) {
            $chan->push(true);
            $chan = $manager->get(3, true);
            $chan->push(true);
            $chan2->push(true);
        });

        $channels = $manager->getChannels();
        foreach ($channels as $id => $channel) {
            $manager->get($id + 3, true);
            $manager->close($id);
        }

        $this->assertSame([4, 3, 5], array_keys($manager->getChannels()));
    }
}

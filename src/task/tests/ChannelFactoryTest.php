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

namespace HyperfTest\Task;

use Hyperf\Engine\Channel;
use Hyperf\Task\ChannelFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ChannelFactoryTest extends TestCase
{
    public function testChannelPop()
    {
        $facotry = new ChannelFactory();

        $channel = $facotry->get(1);

        $this->assertInstanceOf(Channel::class, $channel);
        $this->assertTrue($facotry->has(1));

        $id = uniqid();
        $result = parallel([function () use ($facotry) {
            return $facotry->pop(2);
        }, function () use ($facotry, $id) {
            $facotry->push(2, $id);
        }]);

        $this->assertSame($id, $result[0]);
        $this->assertFalse($facotry->has(2));
    }
}

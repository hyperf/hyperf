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

namespace HyperfTest\Nsq;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Nsq;
use HyperfTest\Nsq\Stub\ContainerStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class NsqTest extends TestCase
{
    public function testPublishAndSubscriber()
    {
        $nsq = new Nsq(ContainerStub::getContainer());
        try {
            $body = uniqid();
            $confirm = new Channel(10);
            Coroutine::create(function () use ($nsq, $confirm) {
                $nsq->subscribe('test', 'test', function (Message $message) use ($confirm) {
                    $confirm->push($message->getBody());
                }, true);
            });

            $res = $nsq->publish('test', $body, confirm: true);

            $this->assertTrue($res);
            $this->assertSame($body, $confirm->pop(5));
        } finally {
            $nsq->stopSubscribe();
        }
    }
}

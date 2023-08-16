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
namespace HyperfTest\AsyncQueue;

use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\JobMessage;
use Hyperf\AsyncQueue\MessageInterface;
use HyperfTest\AsyncQueue\Stub\DemoJob;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MessageTest extends TestCase
{
    public function testMessageSerialize()
    {
        system(__DIR__ . '/async_queue2.2');

        $message = unserialize(file_get_contents(__DIR__ . '/message2.2.cache'));

        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(DemoJob::class, $message->job());
        $this->assertSame(9501, $message->job()->id);

        $serialized = serialize($message);
        $message = unserialize($serialized);
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(DemoJob::class, $message->job());
        $this->assertSame(9501, $message->job()->id);
    }

    public function testJobMessageSerialize()
    {
        $id = rand(0, 9999);
        $message = new JobMessage(
            new DemoJob($id)
        );

        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(DemoJob::class, $message->job());
        $this->assertSame($id, $message->job()->id);
    }
}

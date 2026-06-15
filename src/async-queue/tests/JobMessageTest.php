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
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class JobMessageTest extends TestCase
{
    public function testJobMessageSerialize()
    {
        $id = rand(0, 9999);
        $message = new JobMessage(
            new DemoJob($id)
        );

        $serialized = $message->__serialize();

        $this->assertArrayHasKey('job', $serialized);
        $this->assertArrayHasKey('attempts', $serialized);
        $this->assertInstanceOf(JobInterface::class, $serialized['job']);
        $this->assertSame(0, $serialized['attempts']);

        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(DemoJob::class, $message->job());
        $this->assertSame($id, $message->job()->id);
    }

    public function testJobMessageUnserialize()
    {
        $id = rand(0, 9999);
        $message = new JobMessage(
            new DemoJob($id)
        );

        $serialized = $message->__serialize();

        $this->assertArrayHasKey('job', $serialized);
        $this->assertArrayHasKey('attempts', $serialized);

        $message = unserialize(serialize($message));
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(DemoJob::class, $message->job());
        $this->assertSame($id, $message->job()->id);
        $this->assertSame(0, $message->getAttempts());

        $serialized = [
            'job' => $serialized['job'],
            'attempts' => 3,
        ];
        $message->__unserialize($serialized);

        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(JobInterface::class, $message->job());
        $this->assertInstanceOf(DemoJob::class, $message->job());
        $this->assertSame($id, $message->job()->id);
        $this->assertSame(3, $message->getAttempts());
    }
}

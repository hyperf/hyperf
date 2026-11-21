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

namespace HyperfTest\Nats;

use Hyperf\Nats\Connection;
use Hyperf\Nats\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Message::class)]
class NatsMessageTest extends TestCase
{
    public function testDecodedArrayPayloadDoesNotOverrideStringBody(): void
    {
        $connection = new Connection();

        $message = new Message('hyperf', '{"id":"Hyperf"}', '1', $connection);

        $payload = ['id' => 'Hyperf'];

        $message->setPayload($payload);

        self::assertSame('{"id":"Hyperf"}', $message->getBody());
        self::assertSame(['id' => 'Hyperf'], $message->getPayload());
        self::assertTrue($message->hasPayload());
    }

    public function testDecodedStringPayloadCanRemainBackwardCompatible(): void
    {
        $connection = new Connection();

        $message = new Message('hyperf', '"Hyperf"', '1', $connection);

        $payload = 'Hyperf';

        $message->setPayload($payload);

        if (is_string($payload)) {
            $message->setBody($payload);
        }

        self::assertSame('Hyperf', $message->getBody());
        self::assertSame('Hyperf', $message->getPayload());
    }

    public function testNullPayloadIsAValidDecodedPayload(): void
    {
        $connection = new Connection();

        $message = new Message('hyperf', 'null', '1', $connection);

        $message->setPayload(null);

        self::assertTrue($message->hasPayload());
        self::assertNull($message->getPayload());
        self::assertSame('null', $message->getBody());
    }
}

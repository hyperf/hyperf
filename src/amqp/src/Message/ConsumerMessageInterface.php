<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\Builder\QueueBuilder;

interface ConsumerMessageInterface extends MessageInterface
{
    public function consume($data): string;

    public function setQueue(string $queue);

    public function getQueue(): string;

    public function isRequeue(): bool;

    public function getQos(): ?array;

    public function getQueueBuilder(): QueueBuilder;

    public function getConsumerTag(): string;

    public function isEnable(): bool;

    public function setEnable(bool $enable);
}

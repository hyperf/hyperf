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

namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\Builder\QueueBuilder;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;

interface ConsumerMessageInterface extends MessageInterface
{
    public function consumeMessage($data, AMQPMessage $message): Result;

    public function setQueue(string $queue): static;

    public function getQueue(): string;

    public function isRequeue(): bool;

    public function getQos(): ?array;

    public function getQueueBuilder(): QueueBuilder;

    public function getConsumerTag(): string;

    public function isEnable(): bool;

    public function setEnable(bool $enable): static;

    public function getMaxConsumption(): int;

    public function setMaxConsumption(int $maxConsumption): static;

    public function getWaitTimeout(): float|int;

    public function setWaitTimeout(float|int $timeout): static;

    public function setNums(int $nums): static;

    public function getNums(): int;

    public function setContainer(ContainerInterface $container): static;

    public function getContainer(): ?ContainerInterface;
}

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\DeclareBuilder\QueueDeclareBuilder;

interface ConsumerInterface extends MessageInterface
{
    public function getQueue(): string;

    public function consume($data): bool;

    public function isRequeue(): bool;

    public function getQueueDeclareBuilder(): QueueDeclareBuilder;
}

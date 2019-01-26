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
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Framework\ApplicationContext;

abstract class Consumer extends Message implements ConsumerInterface
{
    protected $queue;

    protected $requeue = true;

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function isRequeue(): bool
    {
        return $this->requeue;
    }

    public function getQueueDeclareBuilder(): QueueDeclareBuilder
    {
        return (new QueueDeclareBuilder())
            ->setQueue($this->getQueue())
        ;
    }

    public function unserialize(string $data)
    {
        $application = ApplicationContext::getContainer();
        $packer = $application->get(Packer::class);

        return $packer->unpack($data);
    }
}

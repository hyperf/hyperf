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
use PhpAmqpLib\Wire\AMQPTable;

/**
 * @method ConsumerMessage getQueue()
 */
trait ConsumerDelayedMessageTrait
{
    /**
     * @var string x-dead-letter-exchange
     */
    protected $deadLetterExchange = 'delayed';

    /**
     * Overwrite.
     */
    public function getQueueBuilder(): QueueBuilder
    {
        return (new QueueBuilder())->setQueue((string) $this->getQueue())
            ->setArguments(new AMQPTable(['x-dead-letter-exchange' => $this->deadLetterExchange]));
    }
}

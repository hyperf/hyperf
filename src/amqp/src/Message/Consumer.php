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

use Hyperf\Amqp\Exceptions\MessageException;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Consumer extends Message implements ConsumerInterface
{
    protected $queue;

    protected $status = true;

    protected $requeue = true;

    protected $signals = [
        SIGQUIT,
        SIGTERM,
        SIGTSTP
    ];

    abstract public function handle($data): bool;

    public function callback(AMQPMessage $msg)
    {
        $packer = $this->getPacker();
        $body = $msg->getBody();
        $data = $packer->unpack($body);

        try {
            if ($this->handle($data)) {
                $this->ack($msg);
            } else {
                $this->reject($msg);
            }
        } catch (\Throwable $ex) {
            $this->catch($ex, $data, $msg);
        }
    }

    public function consume(): void
    {
        pcntl_async_signals(true);

        foreach ($this->signals as $signal) {
            pcntl_signal($signal, [$this, 'signalHandler']);
        }

        $this->channel->basic_consume(
            $this->queue,
            $this->routingKey,
            false,
            false,
            false,
            false,
            [$this, 'callback']
        );

        while ($this->status && count($this->channel->callbacks) > 0) {
            $this->channel->wait();
        }

        $this->channel->close();
    }

    public function signalHandler()
    {
        $this->status = false;
    }

    /**
     * Ack the message.
     */
    protected function ack(AMQPMessage $msg)
    {
        $this->channel->basic_ack($msg->delivery_info['delivery_tag']);
    }

    /**
     * Reject handle the message.
     */
    protected function reject(AMQPMessage $msg)
    {
        $this->channel->basic_reject($msg->delivery_info['delivery_tag'], $this->requeue);
    }

    protected function catch(\Throwable $ex, $data, AMQPMessage $msg)
    {
        return $this->reject($msg);
    }

    protected function check()
    {
        if (!isset($this->queue)) {
            throw new MessageException('queue is required!');
        }

        parent::check();
    }

    protected function declare(): void
    {
        if (!$this->isDeclare()) {
            $this->channel->exchange_declare($this->exchange, $this->type, false, true, false);

            $header = [
                'x-ha-policy' => ['S', 'all']
            ];
            $this->channel->queue_declare($this->queue, false, true, false, false, false, $header);
            $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);

            $key = sprintf('consumer:%s:%s:%s:%s', $this->exchange, $this->type, $this->queue, $this->routingKey);
            $this->getCacheManager()->set($key, 1);
        }
    }

    protected function isDeclare(): bool
    {
        $key = sprintf('consumer:%s:%s:%s:%s', $this->exchange, $this->type, $this->queue, $this->routingKey);
        if ($this->getCacheManager()->has($key)) {
            return true;
        }
        return false;
    }
}

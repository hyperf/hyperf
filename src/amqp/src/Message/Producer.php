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

use Hyperf\Amqp\Constants;
use Hyperf\Amqp\Exceptions\MessageException;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Producer extends Message implements ProducerInterface
{
    protected $data;

    protected $properties = [
        'content_type' => 'text/plain',
        'delivery_mode' => Constants::DELIVERY_MODE_PERSISTENT
    ];

    public function __destruct()
    {
        $this->channel->close();
    }

    public function produce(MessageInterface $message): void
    {
        $data = $this->getData();
        if (!isset($data)) {
            throw new MessageException('data is required!');
        }

        $packer = $this->getPacker();

        $body = $packer->pack($data);
        $msg = new AMQPMessage($body, $this->properties);
        $this->channel->basic_publish($msg, $this->exchange, $this->routingKey);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    protected function declare()
    {
        if (!$this->isDeclare()) {
            $this->channel->exchange_declare($this->exchange, $this->type, false, true, false);

            $key = sprintf('publisher:%s:%s', $this->exchange, $this->type);
            $this->getCacheManager()->set($key, 1);
        }
    }

    protected function isDeclare()
    {
        $key = sprintf('publisher:%s:%s', $this->exchange, $this->type);
        if ($this->getCacheManager()->has($key)) {
            return true;
        }
        return false;
    }
}

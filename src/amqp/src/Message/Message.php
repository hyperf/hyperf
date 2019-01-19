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

use Hyperf\Amqp\CacheManager\CacheInterface;
use Hyperf\Amqp\CacheManager\Memory;
use Hyperf\Amqp\Connection;
use Hyperf\Amqp\Exceptions\MessageException;
use Hyperf\Amqp\Packer\JsonPacker;
use Hyperf\Amqp\Pool\PoolFactory;
use Hyperf\Contract\PackerInterface;
use Hyperf\Framework\ApplicationContext;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Psr\Container\ContainerInterface;

abstract class Message
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $name = 'default';

    protected $exchange;

    protected $type = 'topic';

    protected $routingKey;

    /** @var AbstractConnection */
    protected $connection;

    /** @var AMQPChannel */
    protected $channel;

    /** @var PackerInterface */
    protected $packer;

    /** @var CacheInterface */
    protected $cacheManager;

    public function __construct()
    {
        $this->check();

        $this->container = ApplicationContext::getContainer();

        if (!isset($this->channel)) {
            /** @var Connection $conn */
            $conn = $this->getConnection();
            $this->connection = $conn->getConnection();
            try {
                $this->channel = $this->connection->channel();
            } catch (AMQPRuntimeException $ex) {
                // 获取channel时失败，重连Connection并获取channel
                $this->connection->reconnect();
                $this->channel = $this->connection->channel();
            }
        }

        $this->declare();
    }

    public static function make()
    {
        $args = func_get_args();
        return new static(...$args);
    }

    public function close()
    {
        $this->connection->close();
    }

    /**
     * @return PackerInterface
     */
    public function getPacker(): PackerInterface
    {
        return $this->packer ?? new JsonPacker();
    }

    /**
     * @param PackerInterface $packer
     */
    public function setPacker(PackerInterface $packer)
    {
        $this->packer = $packer;
        return $this;
    }

    /**
     * @return CacheInterface
     */
    public function getCacheManager(): CacheInterface
    {
        return $this->cacheManager ?? new Memory();
    }

    /**
     * @param CacheInterface $cacheManager
     */
    public function setCacheManager(CacheInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
        return $this;
    }

    protected function getConnection(): Connection
    {
        /** @var PoolFactory $factory */
        $factory = $this->container->get(PoolFactory::class);
        $pool = $factory->getAmqpPool($this->name);
        return $pool->get();
    }

    abstract protected function declare();

    /**
     * @throws MessageException
     */
    protected function check()
    {
        if (!isset($this->exchange)) {
            throw new MessageException('exchange is required!');
        }

        if (!isset($this->type)) {
            throw new MessageException('type is required!');
        }

        if (!isset($this->routingKey)) {
            throw new MessageException('routingKey is required!');
        }
    }
}

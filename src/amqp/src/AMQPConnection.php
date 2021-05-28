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
namespace Hyperf\Amqp;

use Hyperf\Engine\Channel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Psr\Log\LoggerInterface;

class AMQPConnection extends AbstractConnection
{
    public const CHANNEL_POOL_LENGTH = 20000;

    public const CONFIRM_CHANNEL_POOL_LENGTH = 10000;

    /**
     * @var Channel
     */
    protected $pool;

    /**
     * @var Channel
     */
    protected $confirmPool;

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * @param null $login_response @deprecated
     * @param AbstractIO $io
     */
    public function __construct(
        string $user,
        string $password,
        string $vhost = '/',
        bool $insist = false,
        string $login_method = 'AMQPLAIN',
        $login_response = null,
        string $locale = 'en_US',
        AbstractIO $io = null,
        int $heartbeat = 0,
        float $connection_timeout = 0,
        float $channel_rpc_timeout = 0.0
    ) {
        parent::__construct($user, $password, $vhost, $insist, $login_method, $login_response, $locale, $io, $heartbeat, $connection_timeout, $channel_rpc_timeout);

        $this->pool = new Channel(static::CHANNEL_POOL_LENGTH);
        $this->confirmPool = new Channel(static::CONFIRM_CHANNEL_POOL_LENGTH);
    }

    /**
     * @return static
     */
    public function setPool(Channel $pool)
    {
        $this->pool = $pool;
        return $this;
    }

    /**
     * @return static
     */
    public function setConfirmPool(Channel $confirmPool)
    {
        $this->confirmPool = $confirmPool;
        return $this;
    }

    /**
     * @return static
     */
    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function getIO()
    {
        return $this->io;
    }

    public function getChannel(): AMQPChannel
    {
        if ($this->pool->isEmpty()) {
            return $this->channel();
        }

        $id = (int) $this->pool->pop(0.001);
        return $this->channel($id);
    }

    public function getConfirmChannel(): AMQPChannel
    {
        if ($this->confirmPool->isEmpty()) {
            $channel = $this->channel();
            $channel->confirm_select();
        } else {
            $id = (int) $this->confirmPool->pop(0.001);
            $channel = $this->channel($id);
        }

        return $channel;
    }

    public function releaseChannel(AMQPChannel $channel, bool $confirm = false): void
    {
        if ($confirm) {
            $this->confirmPool->push($channel->getChannelId());
        } else {
            $this->pool->push($channel->getChannelId());
        }
    }
}

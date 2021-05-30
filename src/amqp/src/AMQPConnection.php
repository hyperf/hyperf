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

use Hyperf\Amqp\IO\SwooleIO;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Coroutine;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Psr\Log\LoggerInterface;

class AMQPConnection extends AbstractConnection
{
    public const CHANNEL_POOL_LENGTH = 20000;

    public const CONFIRM_CHANNEL_POOL_LENGTH = 10000;

    /**
     * @var bool
     */
    public $isBroken = false;

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
     * @var int
     */
    protected $lastChannelId = 0;

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
        int $connection_timeout = 0,
        float $channel_rpc_timeout = 0.0
    ) {
        parent::__construct($user, $password, $vhost, $insist, $login_method, $login_response, $locale, $io, $heartbeat, $connection_timeout, $channel_rpc_timeout);

        $this->pool = new Channel(static::CHANNEL_POOL_LENGTH);
        $this->confirmPool = new Channel(static::CONFIRM_CHANNEL_POOL_LENGTH);
        Coroutine::create(function () {
            if ($this->io instanceof SwooleIO) {
                $this->isBroken = $this->io->isBroken();
            }
        });
    }

    /**
     * @return static
     */
    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;
        if ($this->io instanceof SwooleIO) {
            $this->io->setLogger($logger);
        }
        return $this;
    }

    public function getIO()
    {
        return $this->io;
    }

    public function getChannel(): AMQPChannel
    {
        $id = 0;
        if (! $this->pool->isEmpty()) {
            $id = (int) $this->pool->pop(0.001);
        }

        if ($id === 0) {
            $id = $this->makeChannelId();
        }

        return $this->channel($id);
    }

    public function getConfirmChannel(): AMQPChannel
    {
        $id = 0;
        $confirm = false;
        if (! $this->pool->isEmpty()) {
            $id = (int) $this->confirmPool->pop(0.001);
        }

        if ($id === 0) {
            $id = $this->makeChannelId();
            $confirm = true;
        }

        $channel = $this->channel($id);
        $confirm && $channel->confirm_select();

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

    protected function makeChannelId(): int
    {
        for ($i = 0; $i < $this->channel_max; ++$i) {
            $id = ($this->lastChannelId++ % $this->channel_max) + 1;
            if (! isset($this->channels[$id])) {
                return $id;
            }
        }

        throw new AMQPRuntimeException('No free channel ids');
    }
}

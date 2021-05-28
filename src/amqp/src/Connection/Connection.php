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
namespace Hyperf\Amqp\Connection;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Channel;
use Hyperf\Utils\ApplicationContext;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

class Connection extends AbstractConnection
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var Channel
     */
    protected $confirmChannel;

    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param bool $insist
     * @param string $loginMethod
     * @param null $loginResponse
     * @param string $locale
     * @param float $connectionTimeout
     * @param float $readWriteTimeout
     * @param null $context
     * @param bool $keepalive
     * @param int $heartbeat
     * @param float $channelRpcTimeout
     * @throws \Exception
     */
    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $vhost = '/',
        $insist = false,
        $loginMethod = 'AMQPLAIN',
        $loginResponse = null,
        $locale = 'en_US',
        $connectionTimeout = 3.0,
        $readWriteTimeout = 3.0,
        $context = null,
        $keepalive = false,
        $heartbeat = 0,
        $channelRpcTimeout = 0.0
    ) {
        $io = new SwooleIO($host, $port, $connectionTimeout, $readWriteTimeout, $context, $keepalive, $heartbeat);
        $io->setLogger(ApplicationContext::getContainer()->get(StdoutLoggerInterface::class));
        $this->channel = new Channel(20000);
        $this->confirmChannel = new Channel(10000);
        parent::__construct(
            $user,
            $password,
            $vhost,
            $insist,
            $loginMethod,
            $loginResponse,
            $locale,
            $io,
            $heartbeat,
            (int) $connectionTimeout,
            $channelRpcTimeout
        );
    }

    public function getIO()
    {
        return $this->io;
    }

    public function getChannel(): AMQPChannel
    {
        if ($this->channel->isEmpty()) {
            return $this->channel();
        }

        $id = (int) $this->channel->pop(0.001);
        return $this->channel($id);
    }

    public function getConfirmChannel(): AMQPChannel
    {
        if ($this->confirmChannel->isEmpty()) {
            $channel = $this->channel();
            $channel->confirm_select();
        } else {
            $id = (int) $this->confirmChannel->pop(0.001);
            $channel = $this->channel($id);
        }

        return $channel;
    }

    public function releaseChannel(AMQPChannel $channel, bool $confirm = false): void
    {
        if ($confirm) {
            $this->confirmChannel->push($channel->getChannelId());
        } else {
            $this->channel->push($channel->getChannelId());
        }
    }
}

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

use Hyperf\Amqp\Exception\LoopBrokenException;
use Hyperf\Amqp\Exception\SendChannelClosedException;
use Hyperf\Amqp\Exception\SendChannelTimeoutException;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Channel\Manager as ChannelManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Exception\ChannelClosedException;
use Hyperf\Engine\Channel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Channel\Frame;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Psr\Log\LoggerInterface;
use Throwable;

class AMQPConnection extends AbstractConnection
{
    public const CHANNEL_POOL_LENGTH = 20000;

    public const CONFIRM_CHANNEL_POOL_LENGTH = 10000;

    protected Channel $pool;

    protected Channel $confirmPool;

    protected ?LoggerInterface $logger = null;

    protected int $lastChannelId = 0;

    protected ?Params $params = null;

    protected bool $loop = false;

    protected bool $enableHeartbeat = false;

    protected ChannelManager $channelManager;

    protected bool $exited = false;

    protected Channel $chan;

    public function __construct(
        string $user,
        string $password,
        string $vhost = '/',
        bool $insist = false,
        string $login_method = 'AMQPLAIN',
        $login_response = null,
        string $locale = 'en_US',
        ?AbstractIO $io = null,
        int $heartbeat = 0,
        int $connection_timeout = 0,
        float $channel_rpc_timeout = 0.0
    ) {
        $this->channelManager = new ChannelManager(16);
        $this->channelManager->get(0, true);
        $this->chan = $this->channelManager->make(65535);

        parent::__construct($user, $password, $vhost, $insist, $login_method, $login_response, $locale, $io, $heartbeat, $connection_timeout, $channel_rpc_timeout);

        $this->pool = new Channel(static::CHANNEL_POOL_LENGTH);
        $this->confirmPool = new Channel(static::CONFIRM_CHANNEL_POOL_LENGTH);
    }

    public function write($data): void
    {
        $this->loop();

        $this->chan->push($data, 5);
        if ($this->chan->isClosing()) {
            throw new SendChannelClosedException('Writing data failed, because send channel closed.');
        }
        if ($this->chan->isTimeout()) {
            throw new SendChannelTimeoutException('Writing data failed, because send channel timeout.');
        }
    }

    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    public function setParams(Params $params): static
    {
        $this->params = $params;
        return $this;
    }

    public function getIO(): AbstractIO
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

    public function channel($channel_id = null): AMQPChannel
    {
        $this->channelManager->close($channel_id);
        $this->channelManager->get($channel_id, true);

        return parent::channel($channel_id);
    }

    public function getConfirmChannel(): AMQPChannel
    {
        $id = 0;
        $confirm = false;
        if (! $this->confirmPool->isEmpty()) {
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
        if ($this->params) {
            $length = $confirm ? $this->confirmPool->getLength() : $this->pool->getLength();
            if ($length > $this->params->getMaxIdleChannels()) {
                $channel->close();
                return;
            }
        }

        if ($confirm) {
            $this->confirmPool->push($channel->getChannelId());
        } else {
            $this->pool->push($channel->getChannelId());
        }
    }

    public function close($reply_code = 0, $reply_text = '', $method_sig = [0, 0])
    {
        try {
            return parent::close($reply_code, $reply_text, $method_sig);
        } catch (Throwable $e) {
            if (! $this->exited) {
                throw $e;
            }
        } finally {
            $this->setIsConnected(false);
            $this->chan->close();
            $this->channelManager->flush();
        }
        return null;
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

    protected function loop(): void
    {
        $this->heartbeat();

        if ($this->loop) {
            return;
        }

        $this->loop = true;

        Coroutine::create(function () {
            try {
                while (true) {
                    $data = $this->chan->pop(-1);
                    if ($this->chan->isClosing()) {
                        throw new SendChannelClosedException('Write failed, because send channel closed.');
                    }

                    if ($data === false || $data === '') {
                        throw new LoopBrokenException('Push channel broken or write empty string for connection.');
                    }

                    parent::write($data);
                }
            } catch (Throwable $exception) {
                if (! $this->exited) {
                    $this->logger?->error('Send loop broken. The reason is ' . $exception);
                }
            } finally {
                $this->loop = false;
                if (! $this->exited) {
                    // When loop broken, AMQPConnection will not be able to communicate with AMQP server.
                    // So flush all recv channels to ensure closing AMQP connections quickly.
                    $this->channelManager->flush();
                    $this->close();
                }
            }
        });

        Coroutine::create(function () {
            try {
                while (true) {
                    $frame = $this->wait_frame(0);
                    $this->channelManager->get($frame->getChannel())->push($frame, 0.001);
                }
            } catch (Throwable $exception) {
                if (! $this->exited) {
                    $this->logger?->error('Recv loop broken. The reason is ' . $exception);
                }
            } finally {
                $this->loop = false;
                if (! $this->exited) {
                    // When loop broken, AMQPConnection will not be able to communicate with AMQP server.
                    // So flush all recv channels to ensure closing AMQP connections quickly.
                    $this->channelManager->flush();
                    $this->close();
                }
            }
        });
    }

    protected function wait_channel($channel_id, $timeout = 0): Frame
    {
        $chan = $this->channelManager->get($channel_id);
        if ($chan === null) {
            throw new ChannelClosedException('Wait channel was already closed.');
        }

        $data = $chan->pop($timeout ?: -1);
        if ($data === false) {
            if ($chan->isTimeout()) {
                throw new AMQPTimeoutException('Timeout waiting on channel');
            }
            if ($chan->isClosing()) {
                throw new ChannelClosedException('Wait channel was closed.');
            }
        }

        return $data;
    }

    protected function heartbeat(): void
    {
        if (! $this->enableHeartbeat && $this->getHeartbeat() > 0) {
            $this->enableHeartbeat = true;

            Coroutine::create(function () {
                while (true) {
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($this->getHeartbeat())) {
                        $this->exited = true;
                        $this->close();
                        break;
                    }

                    try {
                        // PING
                        if ($this->isConnected() && $this->chan->isEmpty()) {
                            $pkt = new AMQPWriter();
                            $pkt->write_octet(8);
                            $pkt->write_short(0);
                            $pkt->write_long(0);
                            $pkt->write_octet(0xCE);
                            $this->chan->push($pkt->getvalue(), 0.001);
                        }
                    } catch (Throwable $exception) {
                        $this->logger?->error((string) $exception);
                    }
                }
            });
        }
    }
}

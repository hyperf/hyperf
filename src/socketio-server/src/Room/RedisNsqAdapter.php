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
namespace Hyperf\SocketIOServer\Room;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Nsq;
use Hyperf\Nsq\Nsqd\Api;
use Hyperf\Nsq\Nsqd\Channel;
use Hyperf\Nsq\Result;
use Hyperf\Redis\RedisFactory;
use Hyperf\SocketIOServer\NamespaceInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketServer\Sender;
use Psr\Container\ContainerInterface;

class RedisNsqAdapter extends RedisAdapter
{
    /**
     * @var Nsq
     */
    protected $nsq;

    /**
     * @var string
     */
    protected $pool = 'default';

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $channel;

    public function __construct(ContainerInterface $container, Sender $sender, NamespaceInterface $nsp)
    {
        parent::__construct(
            $container->get(RedisFactory::class),
            $sender,
            $nsp,
            $container->get(SidProviderInterface::class)
        );

        $this->nsq = make(Nsq::class, ['pool' => $this->pool]);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->channel = $this->getChannelKey() . '.' . uniqid();
    }

    public function subscribe()
    {
        Coroutine::create(function () {
            CoordinatorManager::until(Constants::WORKER_START)->yield();
            $nsq = make(Nsq::class, ['pool' => $this->pool]);
            retry(PHP_INT_MAX, function () use ($nsq) {
                $nsq->subscribe($this->getChannelKey(), $this->channel, function (Message $message) {
                    try {
                        [$packet, $opts] = unserialize($message->getBody());
                        $this->doBroadcast($packet, $opts);
                    } catch (\Throwable $exception) {
                        $this->logger->error((string) $exception);
                        throw $exception;
                    }
                    return Result::ACK;
                });
            }, $this->retryInterval);
        });

        Coroutine::create(function () {
            $client = make(Api::class);
            $channelClient = make(Channel::class);
            while (true) {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(10)) {
                    break;
                }

                try {
                    $response = $client->stats('json', $this->getChannelKey());
                    if ($response->getStatusCode() == 200) {
                        $json = Json::decode((string) $response->getBody());
                        foreach ($json['topics'] as $topic) {
                            if (Arr::get($topic, 'topic_name') !== $this->getChannelKey()) {
                                continue;
                            }

                            foreach ($topic['channels'] ?? [] as $channel) {
                                if (empty($channel['clients'])) {
                                    // Delete the channel which don't have clients.
                                    $res = $channelClient->delete($this->getChannelKey(), $channel['channel_name']);
                                }
                            }
                        }
                    }
                } catch (\Throwable $exception) {
                    $this->logger->error((string) $exception);
                }
            }
        });
    }

    protected function publish(string $topic, string $message)
    {
        $this->nsq->publish($topic, $message);
    }

    protected function getChannelKey(): string
    {
        return join('.', [
            $this->redisPrefix,
            str_replace('/', '_', $this->nsp->getNamespace()),
            'channel',
        ]);
    }
}

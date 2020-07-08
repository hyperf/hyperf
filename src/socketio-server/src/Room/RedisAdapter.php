<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\SocketIOServer\Room;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Server\Exception\RuntimeException;
use Hyperf\SocketIOServer\Emitter\Flagger;
use Hyperf\SocketIOServer\NamespaceInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketServer\Sender;
use Mix\Redis\Subscribe\Subscriber;
use Redis;

class RedisAdapter implements AdapterInterface
{
    use Flagger;

    protected $redisPrefix = 'ws';

    protected $retryInterval = 1000;

    protected $connection = 'default';

    /**
     * @var NamespaceInterface
     */
    protected $nsp;

    /**
     * @var \Hyperf\Redis\Redis|Redis|RedisProxy
     */
    protected $redis;

    /**
     * @var SidProviderInterface
     */
    protected $sidProvider;

    /**
     * @var Sender
     */
    protected $sender;

    public function __construct(RedisFactory $redis, Sender $sender, NamespaceInterface $nsp, SidProviderInterface $sidProvider)
    {
        $this->sender = $sender;
        $this->nsp = $nsp;
        $this->redis = $redis->get($this->connection);
        $this->sidProvider = $sidProvider;
    }

    public function add(string $sid, string ...$rooms)
    {
        $this->redis->multi();
        $this->redis->sAdd($this->getSidKey($sid), ...$rooms);
        foreach ($rooms as $room) {
            $this->redis->sAdd($this->getRoomKey($room), $sid);
        }
        $this->redis->sAdd($this->getStatKey(), $sid);
        $this->redis->exec();
    }

    public function del(string $sid, string ...$rooms)
    {
        if (count($rooms) === 0) {
            $clientRooms = $this->clientRooms($sid);
            if (empty($clientRooms)) {
                return;
            }
            $this->del($sid, ...$clientRooms);
            $this->redis->multi();
            $this->redis->del($this->getSidKey($sid));
            $this->redis->sRem($this->getStatKey(), $sid);
            $this->redis->exec();
            return;
        }
        $this->redis->multi();
        $this->redis->sRem($this->getSidKey($sid), ...$rooms);
        foreach ($rooms as $room) {
            $this->redis->sRem($this->getRoomKey($room), $sid);
        }
        $this->redis->exec();
    }

    public function broadcast($packet, $opts)
    {
        $local = data_get($opts, 'flag.local', false);
        if ($local) {
            $this->doBroadcast($packet, $opts);
            return;
        }
        $this->redis->publish($this->getChannelKey(), serialize([$packet, $opts]));
    }

    public function clients(string ...$rooms): array
    {
        $pushed = [];
        $result = [];
        if (! empty($rooms)) {
            foreach ($rooms as $room) {
                $sids = $this->redis->sMembers($this->getRoomKey($room));
                if (! $sids) {
                    continue;
                }
                foreach ($sids as $sid) {
                    if (isset($pushed[$sid])) {
                        continue;
                    }
                    $result[] = $sid;
                    $pushed[$sid] = true;
                }
            }
        } else {
            $sids = $this->redis->sMembers($this->getStatKey());
            foreach ($sids as $sid) {
                $result[] = $sid;
            }
        }
        return $result;
    }

    public function clientRooms(string $sid): array
    {
        return $this->redis->sMembers($this->getSidKey($sid));
    }

    public function subscribe()
    {
        Coroutine::create(function () {
            CoordinatorManager::until(Constants::ON_WORKER_START)->yield();
            retry(PHP_INT_MAX, function () {
                try {
                    $sub = make(Subscriber::class);
                    if ($sub) {
                        $this->mixSubscribe($sub);
                    } else {
                        // Fallback to PhpRedis, which has a very bad blocking subscribe model.
                        $this->phpRedisSubscribe();
                    }
                } catch (\Throwable $e) {
                    $container = ApplicationContext::getContainer();
                    if ($container->has(StdoutLoggerInterface::class)) {
                        $logger = $container->get(StdoutLoggerInterface::class);
                        $logger->error($this->formatThrowable($e));
                    }
                    throw $e;
                }
            }, $this->retryInterval);
        });
    }

    public function cleanUp(): void
    {
        $prefix = join(':', [
            $this->redisPrefix,
            $this->nsp->getNamespace(),
        ]);
        $iterator = null;
        while (true) {
            $keys = $this->redis->scan($iterator, "{$prefix}*");
            if ($keys === false) {
                return;
            }
            if (! empty($keys)) {
                $this->redis->del(...$keys);
            }
        }
    }

    protected function doBroadcast($packet, $opts)
    {
        $rooms = data_get($opts, 'rooms', []);
        $pushed = [];
        if (! empty($rooms)) {
            foreach ($rooms as $room) {
                $sids = $this->redis->sMembers($this->getRoomKey($room));
                foreach ($sids as $sid) {
                    $this->tryPush($sid, $packet, $pushed, $opts);
                }
            }
        } else {
            $sids = $this->redis->sMembers($this->getStatKey());
            foreach ($sids as $sid) {
                $this->tryPush($sid, $packet, $pushed, $opts);
            }
        }
    }

    protected function isLocal(string $sid): bool
    {
        return $this->sidProvider->isLocal($sid);
    }

    protected function getRoomKey(string $room): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNamespace(),
            'rooms',
            $room,
        ]);
    }

    protected function getStatKey(): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNamespace(),
            'stat',
        ]);
    }

    protected function getSidKey(string $sid): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNamespace(),
            'fds',
            $sid,
        ]);
    }

    protected function getChannelKey(): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNamespace(),
            'channel',
        ]);
    }

    protected function getFd(string $sid): int
    {
        return $this->sidProvider->getFd($sid);
    }

    private function tryPush(string $sid, string $packet, array &$pushed, array $opts): void
    {
        $compress = data_get($opts, 'flag.compress', false);
        $wsFlag = $this->guessFlags((bool) $compress);
        $except = data_get($opts, 'except', []);
        $fd = $this->getFd($sid);
        if (in_array($sid, $except)) {
            return;
        }
        if ($this->isLocal($sid) && ! isset($pushed[$fd])) {
            $this->sender->push(
                $fd,
                $packet,
                SWOOLE_WEBSOCKET_OPCODE_TEXT,
                $wsFlag
            );
            $pushed[$fd] = true;
            $this->shouldClose($opts) && $this->close($fd);
        }
    }

    private function formatThrowable(\Throwable $throwable): string
    {
        return (string) $throwable;
    }

    private function phpRedisSubscribe()
    {
        $redis = $this->redis;
        /** @var string $callback */
        $callback = function ($redis, $chan, $msg) {
            Coroutine::create(function () use ($msg) {
                [$packet, $opts] = unserialize($msg);
                $this->doBroadcast($packet, $opts);
            });
        };
        // cast to string because PHPStan asked so.
        $redis->subscribe([$this->getChannelKey()], $callback);
    }

    private function mixSubscribe(Subscriber $sub)
    {
        $sub->subscribe($this->getChannelKey());
        $chan = $sub->channel();
        Coroutine::create(function () use ($sub) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $sub->close();
        });
        while (true) {
            $data = $chan->pop();
            if (empty($data)) { // 手动close与redis异常断开都会导致返回false
                if (! $sub->closed) {
                    throw new RuntimeException('Redis subscriber disconnected from Redis.');
                }
                break;
            }

            Coroutine::create(function () use ($data) {
                [$packet, $opts] = unserialize($data->payload);
                $this->doBroadcast($packet, $opts);
            });
        }
    }

    private function shouldClose(array $opts)
    {
        return data_get($opts, 'flag.close', false);
    }

    private function close(int $fd)
    {
        $this->sender->disconnect($fd);
    }
}

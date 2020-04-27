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

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Server\Exception\RuntimeException;
use Hyperf\SocketIOServer\BaseNamespace;
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
    protected $redisPrefix = 'ws';

    protected $retryInterval = 1000;

    protected $connection = 'default';

    /**
     * @var BaseNamespace
     */
    private $nsp;

    /**
     * @var \Hyperf\Redis\Redis|Redis|RedisProxy
     */
    private $redis;

    /**
     * @var SidProviderInterface
     */
    private $sidProvider;

    /**
     * @var Sender
     */
    private $sender;

    public function __construct(RedisFactory $redis, Sender $sender, BaseNamespace $nsp, SidProviderInterface $sidProvider)
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
            $this->del($sid, ...$this->clientRooms($sid));
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
                    if ($this->isLocal($sid)) {
                        $result[] = $sid;
                        $pushed[$sid] = true;
                    }
                }
            }
        } else {
            $sids = $this->redis->sMembers($this->getStatKey());
            foreach ($sids as $sid) {
                if ($this->isLocal($sid)) {
                    $result[] = $sid;
                }
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
            CoordinatorManager::get(Constants::ON_WORKER_START)->yield();
            retry((int) INF, function () {
                $sub = ApplicationContext::getContainer()->get(Subscriber::class);
                if ($sub) {
                    $this->mixSubscribe($sub);
                } else {
                    // Fallback to PhpRedis, which has a very bad blocking subscribe model.
                    $this->phpRedisSubscribe();
                }
            }, $this->retryInterval);
        });
    }

    public function cleanUp(): void
    {
        $prefix = join(':', [
            $this->redisPrefix,
            $this->nsp->getNsp(),
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
        $except = data_get($opts, 'except', []);
        $volatile = data_get($opts, 'flag.volatile', false);
        $compress = data_get($opts, 'flag.compress', false);
        if ($compress) {
            $wsFlag = SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS;
        } else {
            $wsFlag = SWOOLE_WEBSOCKET_FLAG_FIN;
        }
        $pushed = [];
        if (! empty($rooms)) {
            foreach ($rooms as $room) {
                $sids = $this->redis->sMembers($this->getRoomKey($room));
                foreach ($sids as $sid) {
                    $fd = $this->getFd($sid);
                    if (in_array($sid, $except)) {
                        continue;
                    }
                    if ($this->isLocal($sid)) {
                        $this->sender->push(
                            $fd,
                            $packet,
                            SWOOLE_WEBSOCKET_OPCODE_TEXT,
                            $wsFlag
                        );
                        $pushed[$fd] = true;
                    }
                }
            }
        } else {
            $sids = $this->redis->sMembers($this->getStatKey());
            foreach ($sids as $sid) {
                $fd = $this->getFd($sid);
                if (in_array($sid, $except)) {
                    continue;
                }
                if ($this->isLocal($sid)) {
                    $this->sender->push($fd, $packet, SWOOLE_WEBSOCKET_OPCODE_TEXT, $wsFlag);
                }
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
            $this->nsp->getNsp(),
            'rooms',
            $room,
        ]);
    }

    protected function getStatKey(): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNsp(),
            'stat',
        ]);
    }

    protected function getSidKey(string $sid): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNsp(),
            'fds',
            $sid,
        ]);
    }

    protected function getChannelKey(): string
    {
        return join(':', [
            $this->redisPrefix,
            $this->nsp->getNsp(),
            'channel',
        ]);
    }

    protected function getFd(string $sid): int
    {
        return $this->sidProvider->getFd($sid);
    }

    private function phpRedisSubscribe()
    {
        $redis = $this->redis;
        $callback = function ($redis, $chan, $msg) {
            Coroutine::create(function () use ($msg) {
                [$packet, $opts] = unserialize($msg);
                $this->doBroadcast($packet, $opts);
            });
        };
        $redis->subscribe([$this->getChannelKey()], 'callback');
    }

    private function mixSubscribe(Subscriber $sub)
    {
        $sub->subscribe($this->getChannelKey());
        $chan = $sub->channel();
        if (! $chan) {
            return;
        }
        Coroutine::create(function () use ($sub) {
            CoordinatorManager::get(Constants::ON_WORKER_EXIT)->yield();
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
}

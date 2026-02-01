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

namespace Hyperf\Crontab\Mutex;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Crontab\Crontab;
use Hyperf\Redis\RedisFactory;
use Throwable;

class RedisServerMutexByNodeName implements ServerMutex
{
    /**
     * The unique name for node.
     */
    private ?string $nodeName;

    private static ?string $generatedNodeName = null;

    private Timer $timer;

    public function __construct(private RedisFactory $redisFactory)
    {
        $this->nodeName = $this->getNodeName();
        $this->timer = new Timer();
    }

    /**
     * Attempt to obtain a server mutex for the given crontab.
     */
    public function attempt(Crontab $crontab): bool
    {
        if ($this->nodeName === null) {
            return false;
        }

        $redis = $this->redisFactory->get($crontab->getMutexPool());
        $mutexName = $this->getMutexName($crontab);

        $result = $redis->set($mutexName, $this->nodeName, ['NX', 'EX' => $crontab->getMutexExpires()]);

        if ($result) {
            $this->timer->tick(1, function () use ($mutexName, $redis) {
                if ($redis->expire($mutexName, $redis->ttl($mutexName) + 1) === false) {
                    return Timer::STOP;
                }
            });

            Coroutine::create(function () use ($redis, $mutexName) {
                CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
                $redis->del($mutexName);
            });
            return true;
        }

        return $redis->get($mutexName) === $this->nodeName;
    }

    /**
     * Get the server mutex for the given crontab.
     */
    public function get(Crontab $crontab): string
    {
        return (string) $this->redisFactory->get($crontab->getMutexPool())->get(
            $this->getMutexName($crontab)
        );
    }

    protected function getMutexName(Crontab $crontab)
    {
        return 'hyperf' . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule()) . '-sv';
    }

    protected function getNodeName(): ?string
    {
        if ($node = $this->getServerNode()) {
            return $node->getName();
        }

        if ($name = $this->getNodeNameFromContainer()) {
            return $name;
        }

        return $this->getGeneratedNodeName();
    }

    protected function getServerNode(): ?ServerNodeInterface
    {
        if (ApplicationContext::hasContainer() && ApplicationContext::getContainer()->has(ServerNodeInterface::class)) {
            return ApplicationContext::getContainer()->get(ServerNodeInterface::class);
        }

        return null;
    }

    private function getNodeNameFromContainer(): ?string
    {
        if (! ApplicationContext::hasContainer()) {
            return null;
        }

        $container = ApplicationContext::getContainer();
        $key = self::class . '.server_node';

        if ($container->has($key)) {
            return (string) $container->get($key);
        }

        if ($container instanceof ContainerInterface && $name = $this->generateNodeRandomName()) {
            $container->set($key, $name);
            return $name;
        }

        return null;
    }

    private function getGeneratedNodeName(): ?string
    {
        if (self::$generatedNodeName === null) {
            self::$generatedNodeName = $this->generateNodeRandomName();
        }

        return self::$generatedNodeName;
    }

    private function generateNodeRandomName(): ?string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Throwable) {
            return null;
        }
    }
}

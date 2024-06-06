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

namespace Hyperf\Snowflake\MetaGenerator;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Locker;
use Hyperf\Redis\RedisProxy;
use Hyperf\Snowflake\ConfigurationInterface;
use Hyperf\Snowflake\MetaGenerator;
use Redis;
use Throwable;

use function Hyperf\Support\make;

abstract class RedisMetaGenerator extends MetaGenerator
{
    public const DEFAULT_REDIS_KEY = 'hyperf:snowflake:workerId';

    public const REDIS_EXPIRE = 60 * 60;

    protected ?int $workerId = null;

    protected ?int $dataCenterId = null;

    public function __construct(ConfigurationInterface $configuration, int $beginTimestamp, protected ConfigInterface $config)
    {
        parent::__construct($configuration, $beginTimestamp);
    }

    public function init()
    {
        if (is_null($this->workerId) || is_null($this->dataCenterId)) {
            if (Locker::lock(static::class)) {
                try {
                    $this->initDataCenterIdAndWorkerId();
                } finally {
                    Locker::unlock(static::class);
                }
            }
        }
    }

    public function getDataCenterId(): int
    {
        $this->init();

        return $this->dataCenterId;
    }

    public function getWorkerId(): int
    {
        $this->init();

        return $this->workerId;
    }

    private function initDataCenterIdAndWorkerId(): void
    {
        if (is_null($this->workerId) || is_null($this->dataCenterId)) {
            $pool = $this->config->get(sprintf('snowflake.%s.pool', static::class), 'default');
            $key = $this->config->get(sprintf('snowflake.%s.key', static::class), static::DEFAULT_REDIS_KEY);

            $this->setDataCenterIdAndWorkerId($key, $pool);
        }
    }

    private function setDataCenterIdAndWorkerId(string $key, string $pool): void
    {
        /** @var Redis $redis */
        $redis = make(RedisProxy::class, [
            'pool' => $pool,
        ]);

        $id = $redis->incr($key);

        $workerId = $id % $this->configuration->maxWorkerId();
        $dataCenterId = intval($id / $this->configuration->maxWorkerId()) % $this->configuration->maxDataCenterId();

        $workerIdDataCenterIdKey = sprintf('%s:%d_%d', $key, $workerId, $dataCenterId);
        $result = $redis->set($workerIdDataCenterIdKey, date('Y-m-d H:i:s'), ['NX', 'PX' => static::REDIS_EXPIRE * 1000]);
        if ($result === false) {
            $this->setDataCenterIdAndWorkerId($key, $pool);
        } else {
            $this->workerId = $workerId;
            $this->dataCenterId = $dataCenterId;
            $this->heartbeat($workerIdDataCenterIdKey, $pool);
        }
    }

    private function heartbeat(string $workerIdDataCenterIdKey, $pool): void
    {
        Coroutine::create(function () use ($workerIdDataCenterIdKey, $pool) {
            while (true) {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(5 * 60)) {
                    break;
                }
                try {
                    /** @var Redis $redis */
                    $redis = make(RedisProxy::class, [
                        'pool' => $pool,
                    ]);
                    $redis->set($workerIdDataCenterIdKey, date('Y-m-d H:i:s'), ['PX' => static::REDIS_EXPIRE * 1000]);
                } catch (Throwable $throwable) {
                    ApplicationContext::getContainer()?->get(StdoutLoggerInterface::class)?->error($throwable);
                }
            }
        });
    }
}

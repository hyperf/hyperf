<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Snowflake\MetaGenerator;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisProxy;
use Hyperf\Snowflake\ConfigurationInterface;
use Hyperf\Snowflake\MetaGenerator;

class RedisMilliSecondMetaGenerator extends MetaGenerator
{
    const DEFAULT_REDIS_KEY = 'hyperf:snowflake:workerId';

    protected $workerId;

    protected $dataCenterId;

    public function __construct(ConfigurationInterface $configuration, int $beginTimestamp = self::DEFAULT_BEGIN_SECOND, ConfigInterface $config)
    {
        parent::__construct($configuration, $beginTimestamp * 1000);

        $pool = $config->get('snowflake.' . static::class . '.pool', 'default');

        /** @var \Redis $redis */
        $redis = make(RedisProxy::class, [
            'pool' => $pool,
        ]);

        $key = $config->get(sprintf('snowflake.%s.key', static::class), static::DEFAULT_REDIS_KEY);
        $id = $redis->incr($key);

        $this->workerId = $id % $configuration->maxWorkerId();
        $this->dataCenterId = intval($id / $configuration->maxWorkerId()) % $configuration->maxDataCenterId();
    }

    public function getDataCenterId(): int
    {
        return $this->dataCenterId;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function getTimestamp(): int
    {
        return intval(microtime(true) * 1000);
    }

    public function getNextTimestamp(): int
    {
        $timestamp = $this->getTimestamp();
        while ($timestamp <= $this->lastTimestamp) {
            $timestamp = $this->getTimestamp();
        }

        return $timestamp;
    }
}

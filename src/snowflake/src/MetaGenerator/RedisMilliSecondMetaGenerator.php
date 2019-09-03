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

use Hyperf\Contract\ConfigInterface as HyperfConfig;
use Hyperf\Redis\RedisProxy;
use Hyperf\Snowflake\ConfigInterface;
use Hyperf\Snowflake\MetaGenerator;

class RedisMilliSecondMetaGenerator extends MetaGenerator
{
    const REDIS_KEY = 'hyperf:snowflake:worker';

    protected $workerId;

    protected $dataCenterId;

    public function __construct(HyperfConfig $hConfig, ConfigInterface $config, int $beginTimeStamp = self::DEFAULT_BEGIN_SECOND)
    {
        parent::__construct($config, $beginTimeStamp * 1000);

        $pool = $hConfig->get('snowflake.' . static::class . '.pool', 'default');

        /** @var \Redis $redis */
        $redis = make(RedisProxy::class, [
            'pool' => $pool,
        ]);

        $id = $redis->incr(static::REDIS_KEY);

        $this->workerId = $id % $config->maxWorkerId();
        $this->dataCenterId = intval($id / $config->maxWorkerId()) % $config->maxDataCenterId();
    }

    public function getDataCenterId(): int
    {
        return $this->dataCenterId;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function getTimeStamp(): int
    {
        return intval(microtime(true) * 1000);
    }

    public function getNextTimeStamp(): int
    {
        $timestamp = $this->getTimeStamp();
        while ($timestamp <= $this->lastTimeStamp) {
            $timestamp = $this->getTimeStamp();
        }

        return $timestamp;
    }
}

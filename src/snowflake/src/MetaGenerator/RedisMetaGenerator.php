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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisProxy;
use Hyperf\Snowflake\ConfigurationInterface;
use Hyperf\Snowflake\MetaGenerator;

abstract class RedisMetaGenerator extends MetaGenerator
{
    const DEFAULT_REDIS_KEY = 'hyperf:snowflake:workerId';

    /**
     * @var ConfigInterface
     */
    protected $config;

    protected $workerId;

    protected $dataCenterId;

    public function __construct(ConfigurationInterface $configuration, int $beginTimestamp, ConfigInterface $config)
    {
        parent::__construct($configuration, $beginTimestamp);

        $this->config = $config;
    }

    public function init()
    {
        if (is_null($this->workerId) || is_null($this->dataCenterId)) {
            $pool = $this->config->get(sprintf('snowflake.%s.pool', static::class), 'default');

            /** @var \Redis $redis */
            $redis = make(RedisProxy::class, [
                'pool' => $pool,
            ]);

            $key = $this->config->get(sprintf('snowflake.%s.key', static::class), static::DEFAULT_REDIS_KEY);
            $id = $redis->incr($key);

            $this->workerId = $id % $this->configuration->maxWorkerId();
            $this->dataCenterId = intval($id / $this->configuration->maxWorkerId()) % $this->configuration->maxDataCenterId();
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
}

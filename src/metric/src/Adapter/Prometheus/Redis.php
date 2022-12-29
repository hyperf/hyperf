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
namespace Hyperf\Metric\Adapter\Prometheus;

use Exception;
use Hyperf\Utils\Codec\Json;
use InvalidArgumentException;
use Prometheus\Counter;
use Prometheus\Exception\StorageException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\Adapter;

class Redis implements Adapter
{
    public const PROMETHEUS_METRIC_KEYS_SUFFIX = '_METRIC_KEYS';

    private static array $defaultOptions = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.1,
        'read_timeout' => 10,
        'persistent_connections' => false,
        'password' => null,
    ];

    private static string $prefix = 'PROMETHEUS_';

    private array $options;

    /**
     * @var \Redis
     */
    private mixed $redis;

    private bool $connectionInitialized = false;

    public function __construct(array $options = [])
    {
        $this->options = array_merge(self::$defaultOptions, $options);
        $this->redis = new \Redis();
    }

    /**
     * Create an instance from an established redis connection.
     *
     * @param \Hyperf\Redis\Redis|\Redis $redis
     */
    public static function fromExistingConnection(mixed $redis): self
    {
        $self = new self();
        $self->connectionInitialized = true;
        $self->redis = $redis;
        return $self;
    }

    public static function setDefaultOptions(array $options): void
    {
        self::$defaultOptions = array_merge(self::$defaultOptions, $options);
    }

    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    /**
     * @throws StorageException
     */
    public function flushRedis(): void
    {
        $this->wipeStorage();
    }

    public function wipeStorage(): void
    {
        $this->openConnection();
        $this->redis->flushAll();
    }

    /**
     * @return MetricFamilySamples[]
     * @throws StorageException
     */
    public function collect(): array
    {
        $this->openConnection();
        $metrics = $this->collectHistograms();
        $metrics = array_merge($metrics, $this->collectGauges());
        $metrics = array_merge($metrics, $this->collectCounters());
        return array_map(
            fn (array $metric) => new MetricFamilySamples($metric),
            $metrics
        );
    }

    /**
     * @throws StorageException
     */
    public function updateHistogram(array $data): void
    {
        $this->openConnection();
        $redisTag = $this->getRedisTag(Histogram::TYPE);
        $bucketToIncrease = '+Inf';
        foreach ($data['buckets'] as $bucket) {
            if ($data['value'] <= $bucket) {
                $bucketToIncrease = $bucket;
                break;
            }
        }
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues']);

        $this->redis->eval(
            <<<'LUA'
local increment = redis.call('hIncrByFloat', KEYS[1], ARGV[1], ARGV[3])
redis.call('hIncrBy', KEYS[1], ARGV[2], 1)
if increment == ARGV[3] then
    redis.call('hSet', KEYS[1], '__meta', ARGV[4])
    redis.call('sAdd', KEYS[2], KEYS[1])
end
LUA
            ,
            [
                $this->toMetricKey($data) . $redisTag,
                self::$prefix . Histogram::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX . $redisTag,
                Json::encode(['b' => 'sum', 'labelValues' => $data['labelValues']]),
                Json::encode(['b' => $bucketToIncrease, 'labelValues' => $data['labelValues']]),
                $data['value'],
                Json::encode($metaData),
            ],
            2
        );
    }

    /**
     * @throws StorageException
     */
    public function updateGauge(array $data): void
    {
        $this->openConnection();
        $redisTag = $this->getRedisTag(Gauge::TYPE);
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues'], $metaData['command']);

        $this->redis->eval(
            <<<'LUA'
local result = redis.call(ARGV[1], KEYS[1], ARGV[2], ARGV[3])
if ARGV[1] == 'hSet' then
    if result == 1 then
        redis.call('hSet', KEYS[1], '__meta', ARGV[4])
        redis.call('sAdd', KEYS[2], KEYS[1])
    end
else
    if result == ARGV[3] then
        redis.call('hSet', KEYS[1], '__meta', ARGV[4])
        redis.call('sAdd', KEYS[2], KEYS[1])
    end
end
LUA
            ,
            [
                $this->toMetricKey($data) . $redisTag,
                self::$prefix . Gauge::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX . $redisTag,
                $this->getRedisCommand($data['command']),
                Json::encode($data['labelValues']),
                $data['value'],
                Json::encode($metaData),
            ],
            2
        );
    }

    /**
     * @throws StorageException
     */
    public function updateCounter(array $data): void
    {
        $this->openConnection();
        $redisTag = $this->getRedisTag(Counter::TYPE);
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues'], $metaData['command']);

        $this->redis->eval(
            <<<'LUA'
local result = redis.call(ARGV[1], KEYS[1], ARGV[3], ARGV[2])
if result == tonumber(ARGV[2]) then
    redis.call('hMSet', KEYS[1], '__meta', ARGV[4])
    redis.call('sAdd', KEYS[2], KEYS[1])
end
return result
LUA
            ,
            [
                $this->toMetricKey($data) . $redisTag,
                self::$prefix . Counter::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX . $redisTag,
                $this->getRedisCommand($data['command']),
                $data['value'],
                Json::encode($data['labelValues']),
                Json::encode($metaData),
            ],
            2
        );
    }

    protected function getRedisTag(string $metricType): string
    {
        return match ($metricType) {
            Counter::TYPE => '{counter}',
            Histogram::TYPE => '{histogram}',
            Gauge::TYPE => '{gauge}',
            default => '',
        };
    }

    /**
     * @throws StorageException
     */
    private function openConnection(): void
    {
        if ($this->connectionInitialized === true) {
            return;
        }
        $connectionStatus = $this->connectToServer();
        if ($connectionStatus === false) {
            throw new StorageException("Can't connect to Redis server", 0);
        }
        if ($this->options['password']) {
            $this->redis->auth($this->options['password']);
        }
        if (isset($this->options['database'])) {
            $this->redis->select($this->options['database']);
        }
        $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->options['read_timeout']);
    }

    private function connectToServer(): bool
    {
        try {
            if ($this->options['persistent_connections']) {
                return $this->redis->pconnect(
                    $this->options['host'],
                    $this->options['port'],
                    $this->options['timeout']
                );
            }
            return $this->redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        } catch (\RedisException) {
            return false;
        }
    }

    private function collectHistograms(): array
    {
        $keys = $this->redis->sMembers($this->getMetricGatherKey(Histogram::TYPE));
        sort($keys);
        $histograms = [];
        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll(str_replace($this->redis->_prefix(''), '', $key));
            $histogram = Json::decode($raw['__meta']);
            unset($raw['__meta']);
            $histogram['samples'] = [];
            // Add the Inf bucket so we can compute it later on
            $histogram['buckets'][] = '+Inf';
            $allLabelValues = [];
            foreach (array_keys($raw) as $k) {
                $d = Json::decode($k);
                if ($d['b'] == 'sum') {
                    continue;
                }
                if (count($d['labelValues']) !== count($histogram['labelNames'])) {
                    continue;
                }
                $allLabelValues[] = $d['labelValues'];
            }
            // We need set semantics.
            // This is the equivalent of array_unique but for arrays of arrays.
            $allLabelValues = array_map('unserialize', array_unique(array_map('serialize', $allLabelValues)));
            sort($allLabelValues);
            foreach ($allLabelValues as $labelValues) {
                // Fill up all buckets.
                // If the bucket doesn't exist fill in values from
                // the previous one.
                $acc = 0;
                foreach ($histogram['buckets'] as $bucket) {
                    $bucketKey = Json::encode(['b' => $bucket, 'labelValues' => $labelValues]);
                    if (! isset($raw[$bucketKey])) {
                        $histogram['samples'][] = [
                            'name' => $histogram['name'] . '_bucket',
                            'labelNames' => ['le'],
                            'labelValues' => array_merge($labelValues, [$bucket]),
                            'value' => $acc,
                        ];
                    } else {
                        $acc += $raw[$bucketKey];
                        $histogram['samples'][] = [
                            'name' => $histogram['name'] . '_bucket',
                            'labelNames' => ['le'],
                            'labelValues' => array_merge($labelValues, [$bucket]),
                            'value' => $acc,
                        ];
                    }
                }
                // Add the count
                $histogram['samples'][] = [
                    'name' => $histogram['name'] . '_count',
                    'labelNames' => [],
                    'labelValues' => $labelValues,
                    'value' => $acc,
                ];
                // Add the sum
                $histogram['samples'][] = [
                    'name' => $histogram['name'] . '_sum',
                    'labelNames' => [],
                    'labelValues' => $labelValues,
                    'value' => $raw[Json::encode(['b' => 'sum', 'labelValues' => $labelValues])],
                ];
            }
            $histograms[] = $histogram;
        }
        return $histograms;
    }

    private function collectGauges(): array
    {
        $keys = $this->redis->sMembers($this->getMetricGatherKey(Gauge::TYPE));
        sort($keys);
        $gauges = [];
        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll(str_replace($this->redis->_prefix(''), '', $key));
            $gauge = Json::decode($raw['__meta']);
            unset($raw['__meta']);
            $gauge['samples'] = [];
            foreach ($raw as $k => $value) {
                if (count($gauge['labelNames']) !== count(json_decode($k, true))) {
                    continue;
                }
                $gauge['samples'][] = [
                    'name' => $gauge['name'],
                    'labelNames' => [],
                    'labelValues' => Json::decode($k),
                    'value' => $value,
                ];
            }
            usort($gauge['samples'], fn ($a, $b) => strcmp(implode('', $a['labelValues']), implode('', $b['labelValues'])));
            $gauges[] = $gauge;
        }
        return $gauges;
    }

    private function collectCounters(): array
    {
        $keys = $this->redis->sMembers($this->getMetricGatherKey(Counter::TYPE));
        sort($keys);
        $counters = [];
        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll(str_replace($this->redis->_prefix(''), '', $key));
            $counter = Json::decode($raw['__meta']);
            unset($raw['__meta']);
            $counter['samples'] = [];
            foreach ($raw as $k => $value) {
                if (count($counter['labelNames']) !== count(json_decode($k, true))) {
                    continue;
                }
                $counter['samples'][] = [
                    'name' => $counter['name'],
                    'labelNames' => [],
                    'labelValues' => Json::decode($k),
                    'value' => $value,
                ];
            }
            usort($counter['samples'], fn ($a, $b) => strcmp(implode('', $a['labelValues']), implode('', $b['labelValues'])));
            $counters[] = $counter;
        }
        return $counters;
    }

    private function getRedisCommand(int $cmd): string
    {
        return match ($cmd) {
            Adapter::COMMAND_INCREMENT_INTEGER => 'hIncrBy',
            Adapter::COMMAND_INCREMENT_FLOAT => 'hIncrByFloat',
            Adapter::COMMAND_SET => 'hSet',
            default => throw new InvalidArgumentException('Unknown command'),
        };
    }

    private function toMetricKey(array $data): string
    {
        return implode(':', [self::$prefix, $data['type'], $data['name']]);
    }

    /**
     * Get the indicator collection key.
     *
     * @param mixed $metricType
     * @throws Exception Exception thrown when the incoming metric type does not exist
     */
    private function getMetricGatherKey($metricType): string
    {
        return match ($metricType) {
            Counter::TYPE => self::$prefix . Counter::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX . $this->getRedisTag(Counter::TYPE),
            Histogram::TYPE => self::$prefix . Histogram::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX . $this->getRedisTag(Histogram::TYPE),
            Gauge::TYPE => self::$prefix . Gauge::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX . $this->getRedisTag(Gauge::TYPE),
            default => throw new Exception('Unknown metric type'),
        };
    }
}

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

use Hyperf\Codec\Json;
use Hyperf\Metric\Exception\InvalidArgumentException;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\Adapter;
use RedisException;

class Redis implements Adapter
{
    private static string $metricGatherKeySuffix = ':metric_keys';

    private static string $prefix = 'prometheus:';

    public function __construct(private \Hyperf\Redis\Redis|\Redis $redis)
    {
    }

    /**
     * @return MetricFamilySamples[]
     *
     * @throws RedisException
     */
    public function collect(): array
    {
        $metrics = array_merge(
            $this->collectHistograms(),
            $this->collectGauges(),
            $this->collectCounters(),
        );

        return array_map(
            fn (array $metric) => new MetricFamilySamples($metric),
            $metrics
        );
    }

    /**
     * @throws RedisException
     */
    public function updateHistogram(array $data): void
    {
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues']);

        $bucketToIncrease = '+Inf';
        foreach ($data['buckets'] as $bucket) {
            if ($data['value'] <= $bucket) {
                $bucketToIncrease = $bucket;
                break;
            }
        }

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
                $this->toMetricKey($data),
                $this->getMetricGatherKey(Histogram::TYPE),
                Json::encode(['b' => 'sum', 'labelValues' => $data['labelValues']]),
                Json::encode(['b' => $bucketToIncrease, 'labelValues' => $data['labelValues']]),
                $data['value'],
                Json::encode($metaData),
            ],
            2
        );
    }

    /**
     * @throws RedisException
     */
    public function updateGauge(array $data): void
    {
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
                $this->toMetricKey($data),
                $this->getMetricGatherKey(Gauge::TYPE),
                $this->getRedisCommand($data['command']),
                Json::encode($data['labelValues']),
                $data['value'],
                Json::encode($metaData),
            ],
            2
        );
    }

    /**
     * @throws RedisException
     */
    public function updateCounter(array $data): void
    {
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
                $this->toMetricKey($data),
                $this->getMetricGatherKey(Counter::TYPE),
                $this->getRedisCommand($data['command']),
                $data['value'],
                Json::encode($data['labelValues']),
                Json::encode($metaData),
            ],
            2
        );
    }

    /**
     * @throws RedisException
     */
    public function wipeStorage(): void
    {
        $this->redis->flushAll();
    }

    /**
     * @throws RedisException
     */
    public function flushRedis(): void
    {
        $this->wipeStorage();
    }

    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    public static function setMetricGatherKeySuffix(string $suffix): void
    {
        self::$metricGatherKeySuffix = $suffix;
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
     * @throws RedisException
     */
    private function collectHistograms(): array
    {
        $keys = $this->redis->sMembers($this->getMetricGatherKey(Histogram::TYPE));
        sort($keys);

        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll(str_replace($this->redis->_prefix(''), '', $key));

            $histogram = array_merge(Json::decode($raw['__meta'] ?? '{}'), ['samples' => []]);

            unset($raw['__meta']);
            // Add the Inf bucket, so we can compute it later on
            $histogram['buckets'][] = '+Inf';

            foreach (array_keys($raw) as $k) {
                $d = Json::decode($k);

                if (($d['b'] ?? '') == 'sum' || count($d['labelValues'] ?? []) !== count($histogram['labelNames'] ?? [])) {
                    continue;
                }

                $allLabelValues[] = $d['labelValues'] ?? [];
            }
            // We need set semantics.
            // This is the equivalent of array_unique but for arrays of arrays.
            $allLabelValues = array_map('unserialize', array_unique(array_map('serialize', $allLabelValues ?? [])));
            sort($allLabelValues);

            foreach ($allLabelValues as $labelValues) {
                // Fill up all buckets.
                // If the bucket doesn't exist fill in values from
                // the previous one.
                $acc = 0;
                foreach ($histogram['buckets'] as $bucket) {
                    $bucketKey = Json::encode(['b' => $bucket, 'labelValues' => $labelValues]);
                    if (isset($raw[$bucketKey])) {
                        $acc += $raw[$bucketKey];
                    }

                    $histogram['samples'][] = [
                        'name' => $histogram['name'] . '_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => array_merge($labelValues, [$bucket]),
                        'value' => $acc,
                    ];
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

        return $histograms ?? [];
    }

    /**
     * @throws RedisException
     */
    private function collectGauges(): array
    {
        return $this->collectSamples(Gauge::TYPE);
    }

    /**
     * @throws RedisException
     */
    private function collectCounters(): array
    {
        return $this->collectSamples(Counter::TYPE);
    }

    private function toMetricKey(array $data): string
    {
        return self::$prefix . implode(':', [$data['type'] ?? '', $data['name'] ?? '']) . $this->getRedisTag($data['type'] ?? '');
    }

    private function getMetricGatherKey(string $metricType): string
    {
        return self::$prefix . $metricType . self::$metricGatherKeySuffix . $this->getRedisTag($metricType);
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

    /**
     * @throws RedisException
     */
    private function collectSamples(string $metricType): array
    {
        $keys = $this->redis->sMembers($this->getMetricGatherKey($metricType));

        sort($keys);

        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll(str_replace($this->redis->_prefix(''), '', $key));

            $sample = array_merge(Json::decode($raw['__meta'] ?? '{}'), ['samples' => []]);

            unset($raw['__meta']);

            foreach ($raw as $k => $value) {
                if (count($sample['labelNames'] ?? []) !== count(json_decode($k, true))) {
                    continue;
                }

                $sample['samples'][] = [
                    'name' => $sample['name'],
                    'labelNames' => [],
                    'labelValues' => Json::decode($k),
                    'value' => $value,
                ];
            }

            usort($sample['samples'], fn ($a, $b) => strcmp(implode('', $a['labelValues']), implode('', $b['labelValues'])));

            $samples[] = $sample;
        }

        return $samples ?? [];
    }
}

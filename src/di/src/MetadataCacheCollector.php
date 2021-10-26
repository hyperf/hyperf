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
namespace Hyperf\Di;

class MetadataCacheCollector
{
    /**
     * @var array
     */
    protected $collectors = [];

    public function __construct(array $collectors)
    {
        $this->collectors = $collectors;
    }

    public function addCollector(string $collector)
    {
        $this->collectors = array_unique(array_merge(
            $this->collectors,
            [$collector]
        ));
    }

    public function clear()
    {
        $this->collectors = [];
    }

    public function serialize(): string
    {
        $metadata = [];
        foreach ($this->collectors as $collector) {
            if (is_string($collector) && method_exists($collector, 'serialize')) {
                $metadata[$collector] = call([$collector, 'serialize']);
            }
        }

        return json_encode($metadata);
    }

    public function unserialize($serialized): void
    {
        $metadatas = json_decode($serialized, true) ?? [];
        $collectors = [];
        foreach ($metadatas as $collector => $metadata) {
            if (method_exists($collector, 'deserialize')) {
                call([$collector, 'deserialize'], [$metadata]);
                $collectors[] = $collector;
            }
        }

        $this->collectors = $collectors;
    }
}

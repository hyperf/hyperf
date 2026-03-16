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

namespace Hyperf\Nacos\Protobuf;

use Hyperf\Contract\JsonDeSerializable;
use JsonSerializable;

class ServiceHost implements JsonDeSerializable, JsonSerializable
{
    public function __construct(
        public string $instanceId,
        public string $ip,
        public int $port,
        public float $weight,
        public bool $healthy,
        public bool $enabled,
        public bool $ephemeral,
        public string $clusterName,
        public string $serviceName,
        public array $metadata,
        public int $instanceHeartBeatTimeOut,
        public int $instanceHeartBeatInterval,
        public string $instanceIdGenerator,
        public int $ipDeleteTimeout
    ) {
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        return new static(
            $data['instanceId'] ?? '',
            $data['ip'],
            $data['port'],
            $data['weight'],
            $data['healthy'],
            $data['enabled'],
            $data['ephemeral'],
            $data['clusterName'],
            $data['serviceName'],
            $data['metadata'],
            $data['instanceHeartBeatTimeOut'],
            $data['instanceHeartBeatInterval'],
            $data['instanceIdGenerator'] ?? '',
            $data['ipDeleteTimeout'],
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'instanceId' => $this->instanceId,
            'ip' => $this->ip,
            'port' => $this->port,
            'weight' => $this->weight,
            'healthy' => $this->healthy,
            'enabled' => $this->enabled,
            'ephemeral' => $this->ephemeral,
            'clusterName' => $this->clusterName,
            'serviceName' => $this->serviceName,
            'metadata' => $this->metadata,
            'instanceHeartBeatTimeOut' => $this->instanceHeartBeatTimeOut,
            'instanceHeartBeatInterval' => $this->instanceHeartBeatInterval,
            'instanceIdGenerator' => $this->instanceIdGenerator,
            'ipDeleteTimeout' => $this->ipDeleteTimeout,
        ];
    }
}

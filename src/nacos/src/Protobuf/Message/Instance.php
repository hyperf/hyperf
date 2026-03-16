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

namespace Hyperf\Nacos\Protobuf\Message;

use Hyperf\Contract\JsonDeSerializable;
use JsonSerializable;

class Instance implements JsonDeSerializable, JsonSerializable
{
    public function __construct(
        public string $ip,
        public int $port,
        public float $weight,
        public bool $enabled,
        public bool $healthy,
        public string $clusterName,
        public bool $ephemeral,
        public array $metadata,
        public ?string $serviceName = null,
        public ?string $instanceId = null,
        public ?int $instanceHeartBeatInterval = null,
        public ?int $ipDeleteTimeout = null,
        public ?int $instanceHeartBeatTimeOut = null,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'ip' => $this->ip,
            'port' => $this->port,
            'weight' => $this->weight,
            'enabled' => $this->enabled,
            'healthy' => $this->healthy,
            'clusterName' => $this->clusterName,
            'ephemeral' => $this->ephemeral,
            'metadata' => (object) $this->metadata,
            'serviceName' => $this->serviceName,
            'instanceId' => $this->instanceId,
            'instanceHeartBeatInterval' => $this->instanceHeartBeatInterval,
            'ipDeleteTimeout' => $this->ipDeleteTimeout,
            'instanceHeartBeatTimeOut' => $this->instanceHeartBeatTimeOut,
        ];
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        return new static(
            $data['ip'],
            $data['port'],
            $data['weight'],
            $data['enabled'],
            $data['healthy'],
            $data['clusterName'],
            $data['ephemeral'],
            $data['metadata'],
            $data['serviceName'],
            $data['instanceId'],
            $data['instanceHeartBeatInterval'],
            $data['ipDeleteTimeout'],
            $data['instanceHeartBeatTimeOut'],
        );
    }
}

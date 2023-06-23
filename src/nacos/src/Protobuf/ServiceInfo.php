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

class ServiceInfo implements JsonDeSerializable, JsonSerializable
{
    public function __construct(
        public string $name,
        public string $groupName,
        public string $clusters,
        public int $cacheMillis,
        public array $hosts,
        public int $lastRefTime,
        public string $checksum,
        public bool $allIPs,
        public bool $reachProtectionThreshold,
        public bool $valid,
    ) {
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        $hosts = [];
        foreach ($data['hosts'] ?? [] as $host) {
            $hosts[] = ServiceHost::jsonDeSerialize($host);
        }
        return new static(
            $data['name'],
            $data['groupName'],
            $data['clusters'],
            $data['cacheMillis'],
            $hosts,
            $data['lastRefTime'],
            $data['checksum'],
            $data['allIPs'],
            $data['reachProtectionThreshold'],
            $data['valid'],
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name,
            'groupName' => $this->groupName,
            'clusters' => $this->clusters,
            'cacheMillis' => $this->cacheMillis,
            'hosts' => $this->hosts,
            'lastRefTime' => $this->lastRefTime,
            'checksum' => $this->checksum,
            'allIPs' => $this->allIPs,
            'reachProtectionThreshold' => $this->reachProtectionThreshold,
            'valid' => $this->valid,
        ];
    }
}

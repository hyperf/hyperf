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

class Service implements JsonDeSerializable
{
    /**
     * @param Instance[] $hosts
     */
    public function __construct(
        public int $cacheMillis,
        public array $hosts,
        public string $checksum,
        public int $lastRefTime,
        public string $clusters,
        public string $name,
        public string $groupName,
        public bool $valid,
        public bool $allIPs,
        public bool $reachProtectionThreshold,
    ) {
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        $hosts = [];
        foreach ($data['hosts'] ?? [] as $host) {
            $hosts[] = Instance::jsonDeSerialize($host);
        }

        $data['hosts'] = $hosts;
        return new static(...$data);
    }
}

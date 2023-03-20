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

class ListenContext implements JsonDeSerializable, JsonSerializable
{
    public function __construct(public string $tenant, public string $group, public string $dataId, public string $md5)
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'tenant' => $this->tenant,
            'group' => $this->group,
            'dataId' => $this->dataId,
            'md5' => $this->md5,
        ];
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        return new static(
            $data['tenant'],
            $data['group'],
            $data['dataId'],
            $data['md5'] ?? '',
        );
    }

    public function toKeyString(): string
    {
        return self::getKeyString($this->tenant, $this->group, $this->dataId);
    }

    public static function getKeyString(string $tenant, string $group, string $dataId): string
    {
        return sprintf('%s@@%s@@%s', $dataId, $group, $tenant);
    }
}

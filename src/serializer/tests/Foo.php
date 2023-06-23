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
namespace HyperfTest\Serializer;

use Hyperf\Contract\JsonDeSerializable;
use JsonSerializable;

class Foo implements JsonDeSerializable, JsonSerializable
{
    public function __construct(public int $id, public string $name)
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        return new static($data['id'], $data['name']);
    }
}

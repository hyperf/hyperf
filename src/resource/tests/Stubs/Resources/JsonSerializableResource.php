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
namespace HyperfTest\Resource\Stubs\Resources;

class JsonSerializableResource implements \JsonSerializable
{
    public $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->resource->id,
        ];
    }
}

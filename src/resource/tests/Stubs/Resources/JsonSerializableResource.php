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

use JsonSerializable;

class JsonSerializableResource implements JsonSerializable
{
    public $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->resource->id,
        ];
    }
}

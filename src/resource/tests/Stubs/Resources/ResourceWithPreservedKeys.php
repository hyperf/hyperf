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

class ResourceWithPreservedKeys extends PostResource
{
    public bool $preserveKeys = true;

    public function toArray(): array
    {
        return $this->resource;
    }
}

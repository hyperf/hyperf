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

namespace Hyperf\ResourceGrpc;

use Hyperf\Collection\Collection;
use Hyperf\Resource\Json\ResourceCollection;

class GrpcResourceCollection extends ResourceCollection
{
    public function toMessage()
    {
        /** @var Collection $collection */
        $collection = $this->collection->map->toMessage();

        return $collection->all();
    }
}

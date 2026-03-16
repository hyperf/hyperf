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

class AnonymousGrpcResourceCollection extends GrpcResourceCollection
{
    /**
     * Create a new anonymous resource collection.
     *
     * @param string $collects the name of the resource being collected
     */
    public function __construct(mixed $resource, string $collects)
    {
        $this->collects = $collects;

        parent::__construct($resource);
    }
}

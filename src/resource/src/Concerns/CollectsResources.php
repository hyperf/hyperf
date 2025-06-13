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

namespace Hyperf\Resource\Concerns;

use Hyperf\Collection\Collection;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Resource\Value\MissingValue;
use Hyperf\Stringable\Str;
use Traversable;

use function Hyperf\Support\class_basename;

trait CollectsResources
{
    /**
     * Get an iterator for the resource collection.
     */
    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }

    /**
     * Map the given collection resource into its individual resources.
     *
     * @param mixed $resource
     * @return mixed
     */
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return $resource;
        }

        if (is_array($resource)) {
            $resource = new Collection($resource);
        }

        $collects = $this->collects();

        $this->collection = $collects && ! $resource->first() instanceof $collects
            ? $resource->mapInto($collects)
            : $resource->toBase();

        return $this->isPaginatorResource($resource)
            ? $resource->setCollection($this->collection)
            : $this->collection;
    }

    /**
     * Get the resource that this resource collects.
     *
     * @return null|string
     */
    protected function collects()
    {
        if ($this->collects) {
            return $this->collects;
        }

        if (Str::endsWith(class_basename($this), 'Collection')
            && class_exists($class = Str::replaceLast('Collection', '', $this::class))) {
            return $class;
        }

        return null;
    }

    protected function isPaginatorResource($resource): bool
    {
        return $resource instanceof AbstractPaginator;
    }
}

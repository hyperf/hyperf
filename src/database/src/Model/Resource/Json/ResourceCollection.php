<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Database\Model\Resource\Json;

use Countable;
use Hyperf\Database\Model\Resource\CollectsResources;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Utils\Collection;
use IteratorAggregate;

class ResourceCollection extends JsonResource implements Countable, IteratorAggregate
{
    use CollectsResources;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects;

    /**
     * The mapped collection instance.
     *
     * @var Collection
     */
    public $collection;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->resource = $this->collectResource($resource);
    }

    /**
     * Return the count of items in the resource collection.
     *
     * @return int
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->collection->map->toArray()->all();
    }

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse()
    {
        return $this->resource instanceof AbstractPaginator
                    ? (new PaginatedResourceResponse($this))->toResponse()
                    : parent::toResponse();
    }
}

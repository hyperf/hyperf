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
namespace Hyperf\Resource\Json;

use Countable;
use Hyperf\Collection\Collection;
use Hyperf\Resource\Concerns\CollectsResources;
use Hyperf\Resource\Response\PaginatedResponse;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;

class ResourceCollection extends JsonResource implements Countable, IteratorAggregate
{
    use CollectsResources;

    /**
     * The resource that this resource collects.
     */
    public ?string $collects = null;

    /**
     * The mapped collection instance.
     */
    public ?Collection $collection = null;

    /**
     * Create a new resource instance.
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);

        $this->resource = $this->collectResource($resource);
    }

    /**
     * Return the count of items in the resource collection.
     */
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * Transform the resource into a JSON array.
     */
    public function toArray(): array
    {
        /** @var Collection $collection */
        $collection = $this->collection->map->toArray();
        return $collection->all();
    }

    public function toResponse(): ResponseInterface
    {
        if ($this->isPaginatorResource($this->resource)) {
            return (new PaginatedResponse($this))->toResponse();
        }

        return parent::toResponse();
    }
}

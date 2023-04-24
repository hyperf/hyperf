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
namespace Hyperf\Resource\Response;

use Hyperf\Codec\Json;
use Hyperf\Collection\Collection;
use Hyperf\Context\Context;
use Hyperf\Database\Model\Model;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * Create a new resource response.
     *
     * @param mixed $resource the underlying resource
     */
    public function __construct(public mixed $resource)
    {
    }

    public function toResponse(): ResponseInterface
    {
        return $this->response()
            ->withStatus($this->calculateStatus())
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode($this->wrap(
                $this->resource->resolve(),
                $this->resource->with(),
                $this->resource->additional
            ))));
    }

    /**
     * Wrap the given data if necessary.
     */
    protected function wrap(array|Collection $data, array $with = [], array $additional = []): array
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        }

        if ($this->haveDefaultWrapperAndDataIsUnwrapped($data)) {
            $data = [$this->wrapper() => $data];
        } elseif ($this->haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)) {
            $data = [($this->wrapper() ?? 'data') => $data];
        }

        return array_merge_recursive($data, $with, $additional);
    }

    /**
     * Determine if we have a default wrapper and the given data is unwrapped.
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped(array $data): bool
    {
        return $this->wrapper() && ! array_key_exists($this->wrapper(), $data);
    }

    /**
     * Determine if "with" data has been added and our data is unwrapped.
     */
    protected function haveAdditionalInformationAndDataIsUnwrapped(array $data, array $with, array $additional): bool
    {
        return (! empty($with) || ! empty($additional))
            && (! $this->wrapper()
                || ! array_key_exists($this->wrapper(), $data));
    }

    /**
     * Get the default data wrapper for the resource.
     *
     * @return null|string
     */
    protected function wrapper()
    {
        return $this->resource->wrap;
    }

    protected function calculateStatus(): int
    {
        return $this->resource->resource instanceof Model
        && $this->resource->resource->wasRecentlyCreated ? 201 : 200;
    }

    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}

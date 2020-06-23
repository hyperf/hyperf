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
namespace Hyperf\Resource\Response;

use Hyperf\Database\Model\Model;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Resource\Json\AnonymousResourceCollection;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\MessageResource;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * The underlying resource.
     *
     * @var mixed
     */
    public $resource;

    /**
     * Create a new resource response.
     *
     * @param mixed $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function toResponse()
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

    public function toMessage($resource = false)
    {
        if ($resource === false) {
            $resource = $this->resource;
        }

        $data = $resource->resolve();

        if ($data instanceof Collection) {
            $data = $data->all();
        }

        $wrap = array_merge_recursive($data, $resource->with(), $resource->additional);

        foreach ($wrap as $key => $value) {
            if (($value instanceof JsonResource && is_null($value->resource)) || is_null($value)) {
                unset($wrap[$key]);
                continue;
            }

            if ($value instanceof AnonymousResourceCollection) {
                $wrap[$key] = $value->toMessage();
            }

            if ($value instanceof MessageResource) {
                $wrap[$key] = $this->toMessage($value);
            }
        }

        $except = $resource->expect();

        return new $except($wrap);
    }

    /**
     * Wrap the given data if necessary.
     *
     * @param array|Collection $data
     * @param array $with
     * @param array $additional
     * @return array
     */
    protected function wrap($data, $with = [], $additional = [])
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
     *
     * @param array $data
     * @return bool
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped($data)
    {
        return $this->wrapper() && ! array_key_exists($this->wrapper(), $data);
    }

    /**
     * Determine if "with" data has been added and our data is unwrapped.
     *
     * @param array $data
     * @param array $with
     * @param array $additional
     * @return bool
     */
    protected function haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)
    {
        return (! empty($with) || ! empty($additional)) &&
            (! $this->wrapper() ||
                ! array_key_exists($this->wrapper(), $data));
    }

    /**
     * Get the default data wrapper for the resource.
     *
     * @return string
     */
    protected function wrapper()
    {
        return $this->resource->wrap;
    }

    protected function calculateStatus()
    {
        return $this->resource->resource instanceof Model &&
        $this->resource->resource->wasRecentlyCreated ? 201 : 200;
    }

    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}

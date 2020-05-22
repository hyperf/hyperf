<?php

namespace Hyperf\HttpServer\Resource\Json;

use ArrayAccess;
use Hyperf\Database\Model\JsonEncodingException;
use Hyperf\HttpServer\Contract\Responsable;
use JsonSerializable;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\HttpServer\Resource\DelegatesToResource;
use Hyperf\HttpServer\Resource\ConditionallyLoadsAttributes;

class JsonResource implements ArrayAccess, JsonSerializable, Responsable
{
    use ConditionallyLoadsAttributes, DelegatesToResource;

    /**
     * The resource instance.
     *
     * @var mixed
     */
    public $resource;

    /**
     * The additional data that should be added to the top-level resource array.
     *
     * @var array
     */
    public $with = [];

    /**
     * The additional meta data that should be added to the resource response.
     *
     * Added during response construction by the developer.
     *
     * @var array
     */
    public $additional = [];

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public $wrap = 'data';

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Create a new resource instance.
     *
     * @param mixed ...$parameters
     * @return static
     */
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }

    /**
     * Create new anonymous resource collection.
     *
     * @param mixed $resource
     * @return AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        return tap(new AnonymousResourceCollection($resource, static::class), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

    /**
     * Resolve the resource to an array.
     *
     * @return array
     */
    public function resolve()
    {
        $data = $this->toArray();

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        return $this->filter((array)$data);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray()
    {
        if (is_null($this->resource)) {
            return [];
        }

        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     *
     * @throws JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forResource($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @return array
     */
    public function with()
    {
        return $this->with;
    }

    /**
     * Add additional meta data to the resource response.
     *
     * @param array $data
     * @return $this
     */
    public function additional(array $data)
    {
        $this->additional = $data;

        return $this;
    }

    /**
     * Set the string that should wrap the outer-most resource array.
     *
     * @param string $value
     * @return void
     */
    public function wrap($value)
    {
        $this->wrap = $value;
    }

    /**
     * Disable wrapping of the outer-most resource array.
     *
     * @return void
     */
    public function withoutWrapping()
    {
        $this->wrap = null;
    }

    /**
     * Prepare the resource for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->resolve();
    }

    public function toResponse()
    {
        return (new ResourceResponse($this))->toResponse();
    }
}

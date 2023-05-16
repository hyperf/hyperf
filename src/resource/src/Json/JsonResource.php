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

use ArrayAccess;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Server\ResponseProxyTrait;
use Hyperf\Resource\Concerns\ConditionallyLoadsAttributes;
use Hyperf\Resource\Concerns\DelegatesToResource;
use Hyperf\Resource\JsonEncodingException;
use Hyperf\Resource\Response\Response;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Stringable;

use function Hyperf\Tappable\tap;

class JsonResource implements ArrayAccess, JsonSerializable, Arrayable, Jsonable, ResponseInterface, Stringable
{
    use ConditionallyLoadsAttributes;
    use DelegatesToResource;
    use ResponseProxyTrait;

    /**
     * The additional data that should be added to the top-level resource array.
     */
    public array $with = [];

    /**
     * The additional meta data that should be added to the resource response.
     *
     * Added during response construction by the developer.
     */
    public array $additional = [];

    /**
     * The "data" wrapper that should be applied.
     */
    public ?string $wrap = 'data';

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource the resource instance
     */
    public function __construct(public mixed $resource)
    {
    }

    public function __toString(): string
    {
        return $this->toJson(JSON_UNESCAPED_UNICODE);
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
            $collection->preserveKeys = (new static([]))->preserveKeys;
        });
    }

    /**
     * Resolve the resource to an array.
     */
    public function resolve(): array
    {
        $data = $this->toArray();

        return $this->filter($data);
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
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
     * @throws JsonEncodingException
     */
    public function toJson(int $options = 0): string
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forResource($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Get any additional data that should be returned with the resource array.
     */
    public function with(): array
    {
        return $this->with;
    }

    /**
     * Add additional meta data to the resource response.
     *
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
     */
    public function wrap($value)
    {
        $this->wrap = $value;
        return $this;
    }

    /**
     * Disable wrapping of the outer-most resource array.
     */
    public function withoutWrapping()
    {
        $this->wrap = null;
        return $this;
    }

    /**
     * Prepare the resource for JSON serialization.
     */
    public function jsonSerialize(): mixed
    {
        return $this->resolve();
    }

    public function toResponse(): ResponseInterface
    {
        return (new Response($this))->toResponse();
    }

    public function getResponse(): ResponseInterface
    {
        if ($this->response instanceof ResponseInterface) {
            return $this->response;
        }
        return $this->response = $this->toResponse();
    }
}

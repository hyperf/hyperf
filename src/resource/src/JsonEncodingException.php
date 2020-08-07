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
namespace Hyperf\Resource;

use Hyperf\Resource\Json\JsonResource;
use RuntimeException;

class JsonEncodingException extends RuntimeException
{
    /**
     * Create a new JSON encoding exception for the resource.
     *
     * @return static
     */
    public static function forResource(JsonResource $resource, string $message)
    {
        $model = $resource->resource;

        return new static('Error encoding resource [' . get_class($resource) . '] with model [' . get_class($model) . '] with ID [' . $model->getKey() . '] to JSON: ' . $message);
    }
}

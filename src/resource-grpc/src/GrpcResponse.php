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

use Google\Protobuf\Internal\Message;
use Hyperf\Collection\Collection;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\Response\Response;

class GrpcResponse extends Response
{
    /**
     * @param Collection|false|JsonResource $resource
     */
    public function toMessage(mixed $resource = false): Message
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

            if ($value instanceof AnonymousGrpcResourceCollection) {
                $wrap[$key] = $value->toMessage();
            }

            if ($value instanceof GrpcResource) {
                $wrap[$key] = $this->toMessage($value);
            }
        }

        $except = $resource->expect();

        return new $except($wrap);
    }
}

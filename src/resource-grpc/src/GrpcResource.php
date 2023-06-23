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

use Hyperf\Resource\Json\JsonResource;

use function Hyperf\Tappable\tap;

class GrpcResource extends JsonResource
{
    public function expect(): string
    {
        throw new UndefinedGrpcResourceExpectMessage($this);
    }

    public function toMessage()
    {
        return (new GrpcResponse($this))->toMessage();
    }

    /**
     * Create new anonymous resource collection.
     *
     * @param mixed $resource
     * @return AnonymousGrpcResourceCollection
     */
    public static function collection($resource)
    {
        return tap(
            new AnonymousGrpcResourceCollection($resource, static::class),
            function ($collection) {
                $collection->preserveKeys = (new static([]))->preserveKeys;
            }
        );
    }
}

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
namespace Hyperf\Resource\Grpc;

use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\MessageResource;
use Hyperf\Resource\UndefinedGrpcResourceExceptMessage;

class GrpcResource extends JsonResource implements MessageResource
{
    public function expect(): string
    {
        throw new UndefinedGrpcResourceExceptMessage($this);
    }
}

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
namespace HyperfTest\TransformerGrpc\Stubs\Resources;

use Hyperf\TransformerGrpc\GrpcResource;
use HyperfTest\TransformerGrpc\Stubs\Grpc\AllReply;

class AllReplyResource extends GrpcResource
{
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'users' => HiUserResource::collection($this->users),
        ];
    }

    public function expect(): string
    {
        return AllReply::class;
    }
}

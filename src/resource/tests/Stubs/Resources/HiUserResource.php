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
namespace HyperfTest\Resource\Stubs\Resources;

use Hyperf\Resource\Grpc\GrpcResource;
use HyperfTest\Resource\Stubs\Grpc\HiUser;

class HiUserResource extends GrpcResource
{
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sex' => $this->sex,
        ];
    }

    public function expect(): string
    {
        return HiUser::class;
    }
}

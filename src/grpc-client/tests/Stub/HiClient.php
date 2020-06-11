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
namespace HyperfTest\GrpcClient\Stub;

use Grpc\Info;
use Grpc\UserReply;
use Hyperf\GrpcClient\BaseClient;

class HiClient extends BaseClient
{
    public function sayHello()
    {
        return $this->simpleRequest(
            '/grpc.hi/sayHello',
            new Info(),
            [UserReply::class, 'decode']
        );
    }

    public function sayBug()
    {
        return $this->simpleRequest(
            '/bug',
            new Info(),
            [UserReply::class, 'decode']
        );
    }
}

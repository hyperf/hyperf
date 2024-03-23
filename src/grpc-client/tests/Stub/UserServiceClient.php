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

namespace HyperfTest\GrpcClient\Stub;

use Hyperf\GrpcClient\BaseClient;
use UserService\UserId;
use UserService\UserInfo;

class UserServiceClient extends BaseClient
{
    public function info(UserId $userId)
    {
        return $this->_simpleRequest(
            '/UserService.UserService/info',
            $userId,
            [UserInfo::class, 'decode']
        );
    }
}

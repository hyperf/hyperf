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

namespace HyperfTest\Constants\Stub;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum WarnCode: int
{
    use EnumConstantsTrait;

    #[Message('越权操作')]
    case PERMISSION_DENY = 403;

    /**
     * @Message("不存在")
     */
    case NOT_FOUND = 404;

    /**
     * @Message("Server Error")
     */
    #[Message('系统内部错误')]
    case SERVER_ERROR = 500;
}

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

#[Constants]
enum CannotNewInstance: int
{
    /**
     * @Message("Server Error")
     */
    #[Message('系统内部错误')]
    #[NotFound('不存在的注解')]
    case SERVER_ERROR = 500;
}

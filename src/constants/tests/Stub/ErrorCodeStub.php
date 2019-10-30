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

namespace HyperfTest\Constants\Stub;

use Hyperf\Constants\AbstractConstants;

class ErrorCodeStub extends AbstractConstants
{
    /**
     * @Message("Server Error!")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("SHOW ECHO")
     * @Echo("ECHO")
     */
    const SHOW_ECHO = 501;

    const NO_MESSAGE = 502;

    /**
     * @Message("Params[%s] is invalid.")
     */
    const PARAMS_INVALID = 503;
}

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

class SpecificErrorCodeStub extends ErrorCodeStub
{
    /**
     * @Message("Server Error!")
     */
    const SPECIFIC_SERVER_ERROR = 5001;

    /**
     * @Message("SHOW ECHO")
     * @Echo("ECHO")
     * @HttpStatus(5012)
     */
    const SPECIFIC_SHOW_ECHO = 5012;
}

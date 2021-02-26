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

    /**
     * @Message("error.message")
     */
    const TRANSLATOR_ERROR_MESSAGE = 504;

    /**
     * @Message("error.not_exist")
     */
    const TRANSLATOR_NOT_EXIST = 505;

    /**
     * @Status("Status enabled")
     */
    const STATUS_ENABLE = 1;

    /**
     * @Status("Status disabled")
     */
    const STATUS_DISABLE = 0;

    /**
     * @Type("Type enabled")
     */
    const TYPE_ENABLE = 1;

    /**
     * @Type("Type disabled")
     */
    const TYPE_DISABLE = 0;

    /**
     * @Message("Type1001")
     */
    const TYPE_INT = 1001;

    /**
     * @Message("Type1002.1")
     */
    const TYPE_FLOAT = 1002.1;

    /**
     * @Message("Type1003.1")
     */
    const TYPE_FLOAT_STRING = '1003.1';

    /**
     * @Message("TypeString")
     */
    const TYPE_STRING = 'string';
}

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
use Hyperf\Constants\Annotation\Message;

class ErrorCodeStub extends AbstractConstants
{
    #[Message('Not Found.')]
    public const NOT_FOUND = 404;

    /**
     * @Message("Server Error!")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("SHOW ECHO")
     * @Echo("Don't ECHO")
     */
    #[Message('ECHO', 'echo')]
    public const SHOW_ECHO = 501;

    public const NO_MESSAGE = 502;

    /**
     * @Message("Params[%s] is invalid.")
     */
    public const PARAMS_INVALID = 503;

    /**
     * @Message("error.message")
     */
    public const TRANSLATOR_ERROR_MESSAGE = 504;

    /**
     * @Message("error.not_exist")
     */
    public const TRANSLATOR_NOT_EXIST = 505;

    /**
     * @Status("Status enabled")
     */
    public const STATUS_ENABLE = 1;

    /**
     * @Status("Status disabled")
     */
    public const STATUS_DISABLE = 0;

    /**
     * @Type("Type enabled")
     */
    public const TYPE_ENABLE = 1;

    /**
     * @Type("Type disabled")
     */
    public const TYPE_DISABLE = 0;

    /**
     * @Message("Type1001")
     */
    public const TYPE_INT = 1001;

    /**
     * @Message("Type1002.1")
     */
    public const TYPE_FLOAT = 1002.1;

    /**
     * @Message("Type1003.1")
     */
    public const TYPE_FLOAT_STRING = '1003.1';

    /**
     * @Message("TypeString")
     */
    public const TYPE_STRING = 'string';
}

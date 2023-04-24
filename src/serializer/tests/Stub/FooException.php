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
namespace HyperfTest\Serializer\Stub;

use Exception;

class FooException extends Exception
{
    public function __construct($code = 0, $message = '')
    {
        parent::__construct($message, $code);
    }
}

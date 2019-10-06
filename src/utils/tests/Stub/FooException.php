<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Utils\Stub;

class FooException extends \Exception
{
    public function __construct($code = 0, $message = '')
    {
        parent::__construct($message, $code);
    }
}

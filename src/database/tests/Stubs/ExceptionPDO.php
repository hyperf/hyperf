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

namespace HyperfTest\Database\Stubs;

use PDO;
use PDOException;

class ExceptionPDO extends PDO
{
    public function __construct(public bool $throw)
    {
    }

    public function __destruct()
    {
        $this->throw && throw new PDOException();
    }
}

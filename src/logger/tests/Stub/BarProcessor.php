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

namespace HyperfTest\Logger\Stub;

use Monolog\LogRecord;

class BarProcessor
{
    public function __invoke(array|LogRecord $records)
    {
        $records['extra']['bar'] = true;
        return $records;
    }
}

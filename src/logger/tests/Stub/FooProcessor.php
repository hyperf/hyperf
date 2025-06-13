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
use Monolog\Processor\ProcessorInterface;

class FooProcessor implements ProcessorInterface
{
    public function __construct(protected int $repeat)
    {
    }

    public function __invoke(array|LogRecord $records)
    {
        $records['extra']['message'] = str_repeat($records['message'], $this->repeat);
        return $records;
    }
}

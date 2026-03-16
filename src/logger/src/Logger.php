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

namespace Hyperf\Logger;

use DateTimeZone;
use Hyperf\Contract\StdoutLoggerInterface;
use Monolog\Logger as MonoLogger;

class Logger extends MonoLogger implements StdoutLoggerInterface
{
    public function __construct(string $name, array $handlers = [], array $processors = [], ?DateTimeZone $timezone = null)
    {
        parent::__construct($name, $handlers, $processors, $timezone);

        $this->useLoggingLoopDetection(false);
    }
}

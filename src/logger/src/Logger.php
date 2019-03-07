<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Logger;

use Monolog\Logger as MonoLogger;
use Hyperf\Contract\StdoutLoggerInterface;

class Logger extends MonoLogger implements StdoutLoggerInterface
{
}

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

namespace Hyperf\Nats;

function stream_set_timeout($fp, $seconds, $microseconds)
{
    return \stream_set_timeout($fp, (int) $seconds, (int) $microseconds);
}

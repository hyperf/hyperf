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

namespace Hyperf\Metric\Adapter\Prometheus;

class Constants
{
    public const SCRAPE_MODE = 1;

    public const PUSH_MODE = 2;

    public const CUSTOM_MODE = 3;
}

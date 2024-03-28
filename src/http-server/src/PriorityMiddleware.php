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

namespace Hyperf\HttpServer;

class PriorityMiddleware
{
    public const DEFAULT_PRIORITY = 0;

    public function __construct(public string $middleware, public int $priority = self::DEFAULT_PRIORITY)
    {
    }
}

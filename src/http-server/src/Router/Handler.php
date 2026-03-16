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

namespace Hyperf\HttpServer\Router;

class Handler
{
    /**
     * @param array|callable|string $callback
     */
    public function __construct(public mixed $callback, public string $route, public array $options = [])
    {
    }
}

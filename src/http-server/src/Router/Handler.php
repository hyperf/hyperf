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
     * @var array|callable|string
     */
    public $callback;

    /**
     * @var string
     */
    public $route;

    /**
     * @var array
     */
    public $options;

    public function __construct($callback, string $route, array $options = [])
    {
        $this->callback = $callback;
        $this->route = $route;
        $this->options = $options;
    }
}

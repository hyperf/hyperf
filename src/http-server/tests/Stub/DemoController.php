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

namespace HyperfTest\HttpServer\Stub;

class DemoController
{
    public function __construct()
    {
    }

    public function __invoke()
    {
        return 'Action for an invokable controller.';
    }

    public function __return(...$args)
    {
        return $args;
    }

    public function index(int $id, string $name = 'Hyperf', array $params = [])
    {
        return $this->__return($id, $name, $params);
    }

    public function demo()
    {
        return 'Hello World.';
    }
}

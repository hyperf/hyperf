<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpServer\Stub;

class DemoController
{
    public function __construct()
    {
    }

    public function index(int $id, string $name = 'Hyperf', array $params = [])
    {
        return [$id, $name, $params];
    }
}

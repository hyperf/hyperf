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

use Hyperf\HttpServer\Router\RouteCollector;

class RouteCollectorStub extends RouteCollector
{
    public function mergeOptions(array $origin, array $options)
    {
        return parent::mergeOptions($origin, $options);
    }
}

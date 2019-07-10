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

use Hyperf\HttpServer\CoreMiddleware;

class CoreMiddlewareStub extends CoreMiddleware
{
    public function parseParameters(string $controller, string $action, array $arguments): array
    {
        return parent::parseParameters($controller, $action, $arguments);
    }
}

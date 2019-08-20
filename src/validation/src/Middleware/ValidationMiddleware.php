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

namespace Hyperf\Validation\Middleware;

use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\Validation\Contracts\Validation\ValidatesWhenResolved;
use Psr\Container\ContainerInterface;

class ValidationMiddleware extends CoreMiddleware
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'http');
    }

    public function parseParameters(string $controller, string $action, array $arguments): array
    {
        $params = parent::parseParameters($controller, $action, $arguments);

        foreach ($params as $param) {
            if ($param instanceof ValidatesWhenResolved) {
                $param->validateResolved();
            }
        }

        return $params;
    }
}

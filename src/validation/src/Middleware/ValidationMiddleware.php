<?php

declare(strict_types=1);
/**
 * ValidationMiddleware.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019/7/27 12:06
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation\Middleware;

use Hyperf\HttpServer\CoreMiddleware;
use Psr\Container\ContainerInterface;
use Hyperf\Validation\Contracts\Validation\ValidatesWhenResolved;


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
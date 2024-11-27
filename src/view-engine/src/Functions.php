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

namespace Hyperf\ViewEngine;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Context\ResponseContext;
use Hyperf\Contract\Arrayable;
use Hyperf\View\RenderInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('Hyperf\ViewEngine\view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param null|string $view
     * @param array $mergeData
     */
    function view($view = null, array|Arrayable $data = [], $mergeData = []): FactoryInterface|ViewInterface
    {
        $container = ApplicationContext::getContainer();
        if (interface_exists(ResponseInterface::class) && Context::has(ResponseInterface::class)) {
            $contentType = $container->get(RenderInterface::class)->getContentType();
            ResponseContext::get()->setHeader('content-type', $contentType);
        }

        $factory = $container->get(FactoryInterface::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

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

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('Hyperf\\ViewEngine\\view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param null|string $view
     * @param array|Arrayable $data
     * @param array $mergeData
     * @return FactoryInterface|ViewInterface
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        if (interface_exists(ResponseInterface::class) && Context::has(ResponseInterface::class)) {
            Context::set(
                ResponseInterface::class,
                Context::get(ResponseInterface::class)
                    ->withAddedHeader('content-type', 'text/html')
            );
        }

        /** @var FactoryInterface $factory */
        $factory = ApplicationContext::getContainer()->get(FactoryInterface::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

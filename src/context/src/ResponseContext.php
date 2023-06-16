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
namespace Hyperf\Context;

use Psr\Http\Message\ResponseInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

class ResponseContext
{
    public static function get(?int $coroutineId = null): ResponsePlusInterface
    {
        return Context::get(ResponseInterface::class, $coroutineId);
    }

    public static function set(ResponsePlusInterface $request, ?int $coroutineId = null): ResponsePlusInterface
    {
        return Context::set(ResponseInterface::class, $request, $coroutineId);
    }
}

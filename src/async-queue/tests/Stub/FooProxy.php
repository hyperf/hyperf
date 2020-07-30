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
namespace HyperfTest\AsyncQueue\Stub;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Utils\Context;

class FooProxy
{
    public function dump(...$params)
    {
        Context::set(FooProxy::class, $params);
    }

    /**
     * @AsyncQueueMessage
     */
    public function variadic(...$params)
    {
        Context::set(FooProxy::class, $params);
    }

    /**
     * @AsyncQueueMessage
     * @param mixed $params
     */
    public function async($params)
    {
        Context::set(FooProxy::class, $params);
    }
}

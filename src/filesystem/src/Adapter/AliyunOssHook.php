<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Oss\OssClient {
    function is_resource($resource)
    {
        if (! function_exists('swoole_hook_flags')) {
            return \is_resource($resource) || $resource instanceof \Swoole\Curl\Handler;
        }
        if (swoole_hook_flags() & SWOOLE_HOOK_CURL) {
            return \is_resource($resource) || $resource instanceof \Swoole\Curl\Handler;
        }
        return \is_resource($resource);
    }
}

namespace Oss\Http {
    function is_resource($resource)
    {
        if (! function_exists('swoole_hook_flags')) {
            return \is_resource($resource) || $resource instanceof \Swoole\Curl\Handler;
        }
        if (swoole_hook_flags() & SWOOLE_HOOK_CURL) {
            return \is_resource($resource) || $resource instanceof \Swoole\Curl\Handler;
        }
        return \is_resource($resource);
    }
}

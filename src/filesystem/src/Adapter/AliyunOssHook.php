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
namespace Oss\OssClient {
    use Swoole\Runtime;

    function is_resource($resource)
    {
        if (Runtime::getHookFlags() & SWOOLE_HOOK_CURL) {
            return \is_resource($resource) || $resource instanceof \Swoole\Curl\Handler;
        }
        return \is_resource($resource);
    }
}

namespace Oss\Http {
    use Swoole\Runtime;

    function is_resource($resource)
    {
        if (Runtime::getHookFlags() & SWOOLE_HOOK_CURL) {
            return \is_resource($resource) || $resource instanceof \Swoole\Curl\Handler;
        }
        return \is_resource($resource);
    }
}

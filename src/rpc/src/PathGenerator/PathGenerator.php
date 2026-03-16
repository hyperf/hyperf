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

namespace Hyperf\Rpc\PathGenerator;

use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;

class PathGenerator implements PathGeneratorInterface
{
    public function generate(string $service, string $method): string
    {
        $handledNamespace = explode('\\', $service);
        $handledNamespace = Str::replaceLast('Service', '', end($handledNamespace));
        $path = StrCache::snake($handledNamespace);

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $path . '/' . $method;
    }
}

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
use Hyperf\Utils\Str;

class PathGenerator implements PathGeneratorInterface
{
    public function generate(string $service, string $method): string
    {
        $handledNamespace = explode('\\', $service);
        $handledNamespace = Str::replaceArray('\\', ['/'], end($handledNamespace));
        $handledNamespace = Str::replaceLast('Service', '', $handledNamespace);
        $path = Str::snake($handledNamespace);

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $path . '/' . $method;
    }
}

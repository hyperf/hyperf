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

namespace Hyperf\Rpc;

use Hyperf\Rpc\Contract\PathGeneratorInterface;

class PathGenerator implements PathGeneratorInterface
{
    public function generate(string $service, string $method): string
    {
        $path = str_replace('\\', '/', $service);
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $path . '/' . $method;
    }
}

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

namespace Hyperf\RpcMultiplex\HttpMessage\HostReader;

use Hyperf\RpcMultiplex\Contract\HostReaderInterface;

class NullHostReader implements HostReaderInterface
{
    public function read(): string
    {
        return 'unknown';
    }
}

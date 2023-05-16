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
namespace Hyperf\HttpMessage\Server\Connection;

use Hyperf\Engine\Http\WritableConnection;
use Hyperf\HttpMessage\Server\Chunk\Chunkable;
use Hyperf\HttpMessage\Server\ConnectionInterface;

/**
 * @deprecated since 3.1.0, please use `Hyperf\Engine\Http\WritableConnection` instead.
 */
class SwooleConnection extends WritableConnection implements ConnectionInterface, Chunkable
{
}

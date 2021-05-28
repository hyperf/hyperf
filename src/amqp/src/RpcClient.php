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
namespace Hyperf\Amqp;

use Hyperf\Amqp\Exception\NotSupportedException;
use Hyperf\Amqp\Message\RpcMessageInterface;

class RpcClient extends Builder
{
    public function call(RpcMessageInterface $rpcMessage, int $timeout = 5)
    {
        throw new NotSupportedException('RPC is not supported.');
    }
}

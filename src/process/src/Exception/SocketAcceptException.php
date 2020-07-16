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
namespace Hyperf\Process\Exception;

class SocketAcceptException extends \RuntimeException
{
    public function isTimeout(): bool
    {
        return $this->getCode() === SOCKET_ETIMEDOUT;
    }
}

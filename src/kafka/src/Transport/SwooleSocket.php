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
namespace Hyperf\Kafka\Transport;

class SwooleSocket extends \longlang\phpkafka\Socket\SwooleSocket
{
    public function recv(int $length, ?float $timeout = null): string
    {
        $beginTime = microtime(true);
        if ($timeout === null) {
            $timeout = $this->config->getRecvTimeout();
        }
        $leftTime = $timeout;
        /* @phpstan-ignore-next-line */
        while ($this->socket && ! isset($this->receivedBuffer[$length - 1]) && ($timeout == -1 || $leftTime > 0)) {
            $buffer = $this->socket->recv($timeout);
            if ($buffer === false || $buffer === '') {
                return '';
            }
            $this->receivedBuffer .= $buffer;
            if ($timeout > 0) {
                $leftTime = $timeout - (microtime(true) - $beginTime);
            }
        }

        if (isset($this->receivedBuffer[$length - 1])) {
            $result = substr($this->receivedBuffer, 0, $length);
            $this->receivedBuffer = substr($this->receivedBuffer, $length);

            return $result;
        }

        return '';
    }
}

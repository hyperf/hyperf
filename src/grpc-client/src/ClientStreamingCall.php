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
namespace Hyperf\GrpcClient;

class ClientStreamingCall extends StreamingCall
{
    /**
     * @var bool
     */
    private $received = false;

    public function recv(float $timeout = GrpcClient::GRPC_DEFAULT_TIMEOUT)
    {
        if (! $this->received) {
            $this->received = true;
            return parent::recv($timeout);
        }
        trigger_error('ClientStreamingCall can only recv once!', E_USER_ERROR);
        return false;
    }
}

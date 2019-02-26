<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GrpcClient;

use Hyperf\GrpcServer\Utils\Parser;

class StreamingCall extends BaseCall
{
    public function send($message = null): bool
    {
        if (! $this->streamId) {
            $this->streamId = $this->client->openStream($this->method, Parser::serializeMessage($message));
            return $this->streamId > 0;
        }
        trigger_error(E_USER_WARNING, // warning because it may be a wrong retry
                'You can only send once by a streaming call except connection closed and you retry.');
        return false;
    }

    public function push($message): bool
    {
        if (! $this->streamId) {
            $this->streamId = $this->client->openStream($this->method);
        }
        return $this->client->write($this->streamId, Parser::serializeMessage($message), false);
    }

    public function recv(float $timeout = -1)
    {
        if ($this->streamId <= 0) {
            $recv = false;
        } else {
            $recv = $this->client->recv($this->streamId, $timeout);
            if (! $this->client->isStreamExist($this->streamId)) {
                // stream lost, we need re-push
                $this->streamId = 0;
            }
        }
        return Parser::parseToResultArray($recv, $this->deserialize);
    }

    public function end(): bool
    {
        if (! $this->streamId) {
            return false;
        }
        $ret = $this->client->write($this->streamId, null, true);
        if ($ret) {
            $this->streamId = 0;
        }
        return $ret;
    }
}

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

use Google\Protobuf\Internal\Message;
use Swoole\Http2\Response as RawResponse;

class Response
{
    public Message $message;

    public RawResponse $rawResponse;

    public function __construct(Message $message, RawResponse $rawResponse)
    {
        $this->message = $message;
        $this->rawResponse = $rawResponse;
    }

    public function __call($name, $arguments)
    {
        return $this->getMessage()->{$name}(...$arguments);
    }

}

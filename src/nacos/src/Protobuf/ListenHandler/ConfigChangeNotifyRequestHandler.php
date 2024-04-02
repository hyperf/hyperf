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

namespace Hyperf\Nacos\Protobuf\ListenHandler;

use Closure;
use Hyperf\Nacos\Protobuf\ListenHandlerInterface;
use Hyperf\Nacos\Protobuf\Request\NotifySubscriberResponse;
use Hyperf\Nacos\Protobuf\Request\Request;
use Hyperf\Nacos\Protobuf\Response\Response;

class ConfigChangeNotifyRequestHandler implements ListenHandlerInterface
{
    public function __construct(private Closure $callback)
    {
    }

    public function handle(Response $response): void
    {
        $callback = $this->callback;
        $callback($response);
    }

    public function ack(Response $response): Request
    {
        return new NotifySubscriberResponse($response->requestId);
    }
}

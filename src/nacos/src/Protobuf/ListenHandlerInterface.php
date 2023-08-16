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
namespace Hyperf\Nacos\Protobuf;

use Hyperf\Nacos\Protobuf\Request\Request;
use Hyperf\Nacos\Protobuf\Response\Response;

interface ListenHandlerInterface
{
    public function handle(Response $response): void;

    public function ack(Response $response): Request;
}

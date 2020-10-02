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
namespace Hyperf\Contract;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $response, Response $swooleResponse, bool $withContent = true);
}

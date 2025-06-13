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

interface ResponseEmitterInterface
{
    /**
     * @param mixed $connection swoole response or swow session
     */
    public function emit(ResponseInterface $response, mixed $connection, bool $withContent = true): void;
}

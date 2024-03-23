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

namespace Hyperf\GrpcServer;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;
use Throwable;

class ResponseEmitter extends \Hyperf\HttpServer\ResponseEmitter
{
    /**
     * @param Response $connection
     */
    public function emit(ResponseInterface $response, mixed $connection, bool $withContent = true): void
    {
        try {
            if (method_exists($connection, 'isWritable') && ! $connection->isWritable()) {
                return;
            }
            parent::emit($response, $connection, $withContent);
        } catch (Throwable $exception) {
            $this->logger?->critical((string) $exception);
        }
    }
}
